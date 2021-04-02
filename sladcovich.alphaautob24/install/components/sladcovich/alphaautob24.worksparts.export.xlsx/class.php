<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use \Bitrix\Main\Engine\Contract\Controllerable;
use \Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;

Loader::includeModule('sladcovich.alphaautob24');

Loc::loadMessages(__FILE__);

class Alphaautob24WorksPartsExportXlsxComponent extends CBitrixComponent implements Controllerable
{
    /* Базовые методы компонента */
    /**
     * Метод из интерфейса Controllerable для реализации AJAX
     *
     * @return array[][]
     */
    public function configureActions()
    {
        return [
            'createDocumentXLSX' => ['createDocumentXLSX' => []],
        ];
    }

    /**
     * Метод из наследуемого класса CBitrixComponent - Выполнение компонента
     *
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    /* Пользовательские методы компонента */
    /**
     * Перевод суммы в рублях из (float) в писменное представление
     *
     * @param $sum
     * @return string
     */
    public function sumToString($sum)
    {
        {
            $nul = 'ноль';
            $ten = array(
                array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
                array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять')
            );
            $a20 = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать');
            $tens = array(2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
            $hundred = array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
            $unit = array(
                array('копейка', 'копейки', 'копеек', 1),
                array('рубль', 'рубля', 'рублей', 0),
                array('тысяча', 'тысячи', 'тысяч', 1),
                array('миллион', 'миллиона', 'миллионов', 0),
                array('миллиард', 'миллиарда', 'миллиардов', 0),
            );

            list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($sum)));
            $out = array();
            if (intval($rub) > 0) {
                foreach (str_split($rub, 3) as $uk => $v) {
                    if (!intval($v)) continue;
                    $uk = sizeof($unit) - $uk - 1;
                    $gender = $unit[$uk][3];
                    list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
                    // mega-logic
                    $out[] = $hundred[$i1]; // 1xx-9xx
                    if ($i2 > 1) $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3]; // 20-99
                    else $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; // 10-19 | 1-9
                    // units without rub & kop
                    if ($uk > 1) $out[] = self::morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
                }
            } else {
                $out[] = $nul;
            }
            $out[] = self::morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub
            $out[] = $kop . ' ' . self::morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
            return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
        }
    }

    /**
     * Склоняем словоформу
     * @author runcore
     */
    public function morph($n, $f1, $f2, $f5)
    {
        $n = abs(intval($n)) % 100;
        if ($n > 10 && $n < 20) return $f5;
        $n = $n % 10;
        if ($n > 1 && $n < 5) return $f2;
        if ($n == 1) return $f1;
        return $f5;
    }

    /**
     * Получаем данные для ячеек электронной таблицы XLSX для файла экспорта
     *
     * @param $dealId
     * @return array
     */
    public function getDataForDocumentXLSX($dealId)
    {
        global $USER_FIELD_MANAGER;
        global $USER;

        // Собираем текущую дату (пример: 1 марта 2021 г.)
        $date = new DateTime();
        $months = [
            'January' => 'Января',
            'February' => 'Февраля',
            'March' => 'Марта',
            'April' => 'Апреля',
            'May' => 'Мая',
            'June' => 'Июня',
            'July' => 'Июля',
            'August' => 'Августа',
            'September' => 'Сентября',
            'October' => 'Октября',
            'November' => 'Ноября',
            'December' => 'Декабря',
        ];
        $documentDate = $date->format('j') . ' ' . $months[$date->format('F')] . ' ' . $date->format('Y') . ' г.';

        // Получаем сокращенное ФИО текущего пользователя
        $rsUser = CUser::GetByID($USER->getId());
        $arUser = $rsUser->Fetch();
        $surname = $arUser['LAST_NAME'];
        $name = mb_substr($arUser['NAME'], 0, 1) . '.';
        $patronymic = mb_substr($arUser['SECOND_NAME'], 0, 1) . '.';
        $shortFIO = $surname . ' ' . $name . $patronymic;

        // Получаем все работы и общую их сумму
        $componentClass = CBitrixComponent::includeComponentClass('sladcovich:alphaautob24.work');
        $worksData = $componentClass::getAllWorksByDealIdForXLSXDocument($dealId);

        // Получаем все запчасти и общую их сумму
        $componentClass = CBitrixComponent::includeComponentClass('sladcovich:alphaautob24.part');
        $partsData = $componentClass::getAllPartsByDealIdForXLSXDocument($dealId);

        $dataXLSX = [];

        $dataXLSX = [
            'META' => [
                // some common file meta parameters ...
            ],
            'STATIC' => [
                'HEADER_BLOCK' => [
                    'HEADER_ORDER_NUMBER_TITLE' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_HEADER_ORDER_NUMBER'),
                    'HEADER_FROM_TITLE' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_HEADER_FROM'),
                    'HEADER_LOSS_TITLE' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_HEADER_LOSS'),
                    'HEADER_EXECUTOR_TITLE' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_HEADER_EXECUTOR'),
                    'HEADER_OOO_TRUCK_GROUP' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_HEADER_OOO_TRUCK_GROUP'),
                    'HEADER_PRICE_REPAIR_AMTC' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_HEADER_PRICE_REPAIR_AMTC'),
                    'HEADER_RELEASE_DATE' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_HEADER_RELEASE_DATE'),
                    'HEADER_STATE_NUMBER' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_HEADER_STATE_NUMBER'),
                    'HEADER_VIN' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_HEADER_VIN'),
                    'HEADER_BODY_CHASSIS_NUMBER' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_HEADER_BODY_CHASSIS_NUMBER'),
                    'HEADER_BODY_COLOR' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_HEADER_BODY_COLOR'),
                    'HEADER_TECH_PASSPORT' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_HEADER_TECH_PASSPORT'),
                    'HEADER_OWNED_BY' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_HEADER_OWNED_BY'),
                    'HEADER_CONFIDANT' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_HEADER_CONFIDANT'),
                ],
                'WORKS_BLOCK' => [
                    'WORKS_TOTAL_PAINTING_AND_REPAIR_WORKS' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_WORKS_TOTAL_PAINTING_AND_REPAIR_WORKS'),
                    'WORKS_TH_NUMBER_PROGRAM' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_WORKS_TH_NUMBER_PROGRAM'),
                    'WORKS_TH_WORK_NAME' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_WORKS_TH_WORK_NAME'),
                    'WORKS_TH_PRICE_NH' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_WORKS_TH_PRICE_NH'),
                    'WORKS_TH_TIME_NORM' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_WORKS_TH_TIME_NORM'),
                    'WORKS_TH_COUNT' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_WORKS_TH_COUNT'),
                    'WORKS_TH_SUM' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_WORKS_TH_SUM'),
                    'WORKS_TOTAL_PRICE_PAINTING_AND_REPAIR' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_WORKS_TOTAL_PRICE_PAINTING_AND_REPAIR'),
                ],
                'PARTS_BLOCK' => [
                    'PARTS_TOTAL_PAINTING_AND_REPAIR_WORKS' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_PARTS_TOTAL_PAINTING_AND_REPAIR_WORKS'),
                    'PARTS_TH_CATEGORY_NUMBER' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_PARTS_TH_CATEGORY_NUMBER'),
                    'PARTS_TH_NUMBER_NAME' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_PARTS_TH_NAME'),
                    'PARTS_TH_NUMBER_PRICE' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_PARTS_TH_PRICE'),
                    'PARTS_TH_NUMBER_COUNT' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_PARTS_TH_COUNT'),
                    'PARTS_TH_NUMBER_SUM' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_PARTS_TH_SUM'),
                    'PARTS_TOTAL_PRICE_PAINTING_AND_REPAIR' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_PARTS_TOTAL_PRICE_PAINTING_AND_REPAIR'),
                    'PARTS_TOTAL' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_PARTS_TOTAL'),
                ],
                'FOOTER_BLOCK' => [
                    'FOOTER_PRICE_ELIMINATION_OF_DEFECTS' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_FOOTER_PRICE_ELIMINATION_OF_DEFECTS'),
                    'FOOTER_TOTAL_TO_PAY' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_FOOTER_TOTAL_TO_PAY'),
                    'FOOTER_NDS_SUM' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_FOOTER_NDS_SUM'),
                    'FOOTER_COMMON_WORKS_DESCRIPTION' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_FOOTER_COMMON_WORKS_DESCRIPTION'),
                    'FOOTER_MASTER' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WPEX_FOOTER_MASTER'),
                ],
            ],
            'DYNAMIC' => [
                'HEADER_BLOCK' => [
                    'DEAL_ID' => $dealId,
                    'ORDER_AN_OUTFIT_DATE' => $documentDate,
                    'LOSS' => $USER_FIELD_MANAGER->GetUserFieldValue('CRM_DEAL', 'UF_LOSS', $dealId),
                    'RELEASE_DATE' => '', # don't used
                    'STATE_NUMBER' => $USER_FIELD_MANAGER->GetUserFieldValue('CRM_DEAL', 'UF_STATE_NUMBER', $dealId),
                    'VIN' => $USER_FIELD_MANAGER->GetUserFieldValue('CRM_DEAL', 'UF_VIN', $dealId),
                    'BODY_CHASSIS_NUMBER' => '', # don't used
                    'BODY_COLOR' => '', # don't used
                    'TECH_PASSPORT' => '', # don't used
                    'OWNED_BY' => '', # don't used
                    'CONFIDANT' => '', # don't used
                ],
                'WORKS_BLOCK' => [
                    'WORKS' => $worksData, # все работы текущей сделки alphaautob24.works
                    'TOTAL_SUM_WORKS' => $worksData['TOTAL_WORKS_SUM'], # сумма всех работ текущей сделки alphaautob24.works
                ],
                'PARTS_BLOCK' => [
                    'PARTS' => $partsData, # все запчасти текущей сделки alphaautob24.works
                    'TOTAL_SUM_PARTS' => $partsData['TOTAL_PARTS_SUM'], # сумма всех запчастей текущей сделки alphaautob24.works
                ],
                'FOOTER_BLOCK' => [
                    'TOTAL_SUM_WORKS_AND_PARTS' => ($worksData['TOTAL_WORKS_SUM'] + $partsData['TOTAL_PARTS_SUM']), # общая сумма (работы + запчасти)
                    'NDS_SUM' => (($worksData['TOTAL_WORKS_SUM'] + $partsData['TOTAL_PARTS_SUM']) / 100 * 20), # НДС
                    'MASTER_SHORT_NAME' => $shortFIO,
                ],
            ],
        ];

        return $dataXLSX;
    }

    /**
     * Создание шаблона файла XLSX
     *
     * @param $dataXLSX
     */
    public function generatePatternDocumentXLSX($dataXLSX)
    {
        ###############################################################################################################
        // Создаем таблицу и выбираем активный лист (первый) - START

        // Создаем таблицу и выбираем активный лист (первый)
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Создаем таблицу и выбираем активный лист (первый) - END
        ###############################################################################################################
        // Стили для ячеек таблицы - START

        /*
        Правила наименования переменных массивов стилей ячеек

        1) SA - style array
        2) html тег по смыслу
        3) выравнивание по горизонтали
        4) выравнивание по вертикали
        5) размер шрифта
        6) вес шрифта
        7) границы
         */

        /* общие стили */
        $SA_text = [
            'font' => [
                'size' => 10,
                'name' => 'Arial',
            ],
        ];

        /* БЛОК - шапка */
        # Н А Р Я Д  -  З А К А З №
        $SA_h1_center_center_NO_NO_NO = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        # Тг00005273, 1 марта 2021 г.
        $SA_p_center_center_NO_NO_bottom = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ]
        ];

        # от
        $SA_p_center_center_NO_NO_NO = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        # АТ10689199/1
        $SA_h1_center_center_NO_NO_bottom = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOTTED,
                ],
            ]
        ];

        # Убыток №, исполнитель, о стоимости ремонта АМТС, год и месяц выпуска, гос. номер, идентификационный номер (VIN),
        # номер кузова (шасси), цвет кузова, технический паспорт (свидетельство о регистрации), принадлежащего, доверенное лицо
        $SA_p_left_center_NO_NO_NO = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        # ООО "Трак Групп"
        $SA_p_left_top_NO_bold_NO = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
            ],
            'font' => [
                'bold' => true,
            ],
        ];

        # 01.01.2019 0:00:00, е652рв799, Z6FDXXESGDKY35155
        $SA_p_left_center_NO_bold_NO = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
            ],
        ];

        /* БЛОК - работы */
        # СТОИМОСТЬ ОКРАСОЧНЫХ, РЕМОНТНЫХ РАБОТ/РАЗБОРКИ СБОРКИ
        $SA_h1_left_bottom_NO_bold_NO = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM,
            ],
            'font' => [
                'bold' => true,
            ],
        ];

        # № прогр., Наименование работ, Цена Н/Ч, Норма вр., Кол-во, Сумма руб.
        $SA_p_center_center_NO_bold_all = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ]
        ];

        # Наименование работ ~ НАДБАВКА ВРЕМЕНИ ОСНОВНЫЕ ОПЕРАЦИИ
        $SA_h1_left_bottom_NO_NO_all = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ]
        ];

        # Цена Н/Ч ~ 800, Норма вр. ~ 1, Кол-во ~ 0,5
        $SA_p_center_center_NO_NO_all = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ]
        ];

        # Сумма руб. ~ 400
        $SA_p_right_center_NO_NO_all = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ]
        ];

        # ИТОГО стоимость окраски/ремонта/замены:, 14560
        $SA_p_right_center_NO_bold_all = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ]
        ];

        /* БЛОК - запчасти */
        # СТОИМОСТЬ ЗАПАСНЫХ ЧАСТЕЙ И МАТЕРИАЛОВ
        $SA_h1_left_top_NO_bold_NO = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
            ],
        ];

        # Цена руб. ~ 29011,35
        $SA_p_right_bottom_NO_NO_all = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ]
        ];

        /* БЛОК - подвал */

        # ВСЕГО:
        $SA_h1_center_center_NO_bold_NO = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
            ],
        ];

        # << общая таблица >>
        $SA_NO_NO_NO_NO_NO_all = [
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        # ИТОГО Стоимость устранения дефектов  в рублях: ~ 37769,08
        $SA_p_right_center_NO_bold_NO = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
            ],
        ];

        # ВСЕГО К ОПЛАТЕ: ~ 37769,08
        $SA_h1_right_center_NO_bold_all = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        # Мастер
        $SA_p_right_center_NO_NO_NO = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        # Сумма прописью
        $SA_p_left_center_NO_bold_all = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
            ],
        ];


        # << подчеркивание >>
        $SA_NO_NO_NO_NO_NO_bottom = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        /* Задаем ширину столбцам  (ширина php [1] = ~[7] ширина google spreadsheet) */
        $sheet->getColumnDimension('A')->setWidth(14.28);
        $sheet->getColumnDimension('B')->setWidth(42.85);
        $sheet->getColumnDimension('C')->setWidth(8.85);
        $sheet->getColumnDimension('D')->setWidth(8.85);
        $sheet->getColumnDimension('E')->setWidth(8.85);
        $sheet->getColumnDimension('F')->setWidth(8.85);
        $sheet->getColumnDimension('G')->setWidth(8.85);
        $sheet->getColumnDimension('H')->setWidth(11.28);

        /* Задаем высоту для всех строк (высота php [1] = ~[1.26] высота google spreadsheet ;-1 значит auto) */
        $sheet->getDefaultRowDimension()->setRowHeight(-1);
        $sheet->getRowDimension('1')->setRowHeight(39.68);

        /* Применяем общие стили */
        $sheet->getStyle('A1:H200')->applyFromArray($SA_text);

        // Стили для ячеек таблицы - END
        ###############################################################################################################
        // Вставка значений в ячейки таблицы - START

        /* Получение всех данных о сделке */
        ### здесь должна быть функция ###

        /* БЛОК - шапка */
        # строк 1
        $sheet->setCellValue('B1', $dataXLSX['STATIC']['HEADER_BLOCK']['HEADER_ORDER_NUMBER_TITLE']);
        $sheet->getStyle('B1')->applyFromArray($SA_h1_center_center_NO_NO_NO);

        $sheet->setCellValue('C1', $dataXLSX['DYNAMIC']['HEADER_BLOCK']['DEAL_ID']);
        $sheet->getStyle('C1')->applyFromArray($SA_p_center_center_NO_NO_bottom);
        $sheet->mergeCells('C1:D1');

        $sheet->setCellValue('E1', $dataXLSX['STATIC']['HEADER_BLOCK']['HEADER_FROM_TITLE']);
        $sheet->getStyle('E1')->applyFromArray($SA_p_center_center_NO_NO_NO);

        $sheet->setCellValue('F1', $dataXLSX['DYNAMIC']['HEADER_BLOCK']['ORDER_AN_OUTFIT_DATE']);
        $sheet->getStyle('F1')->applyFromArray($SA_p_center_center_NO_NO_bottom);
        $sheet->mergeCells('F1:H1');

        # строка 2
        $sheet->setCellValue('E2', $dataXLSX['STATIC']['HEADER_BLOCK']['HEADER_LOSS_TITLE']);
        $sheet->getStyle('E2')->applyFromArray($SA_p_left_center_NO_NO_NO);

        $sheet->setCellValue('F2', $dataXLSX['DYNAMIC']['HEADER_BLOCK']['LOSS']);
        $sheet->getStyle('F2')->applyFromArray($SA_h1_center_center_NO_NO_bottom);
        $sheet->mergeCells('F2:H2');

        # строка 3
        $sheet->setCellValue('A3', $dataXLSX['STATIC']['HEADER_BLOCK']['HEADER_EXECUTOR_TITLE']);
        $sheet->getStyle('A3')->applyFromArray($SA_p_left_center_NO_NO_NO);

        $sheet->setCellValue('C3', $dataXLSX['STATIC']['HEADER_BLOCK']['HEADER_OOO_TRUCK_GROUP']);
        $sheet->getStyle('C3')->applyFromArray($SA_p_left_top_NO_bold_NO);
        $sheet->mergeCells('C3:H3');

        # строка 4
        $sheet->setCellValue('A4', $dataXLSX['STATIC']['HEADER_BLOCK']['HEADER_PRICE_REPAIR_AMTC']);
        $sheet->getStyle('A4')->applyFromArray($SA_p_left_center_NO_NO_NO);

        # строка 5
        $sheet->setCellValue('A5', $dataXLSX['STATIC']['HEADER_BLOCK']['HEADER_RELEASE_DATE']);
        $sheet->getStyle('A5')->applyFromArray($SA_p_left_center_NO_NO_NO);

        $sheet->setCellValue('C5', $dataXLSX['DYNAMIC']['HEADER_BLOCK']['RELEASE_DATE']);
        $sheet->getStyle('C5')->applyFromArray($SA_p_left_center_NO_bold_NO);
        $sheet->mergeCells('C5:H5');

        # строка 6
        $sheet->setCellValue('A6', $dataXLSX['STATIC']['HEADER_BLOCK']['HEADER_STATE_NUMBER']);
        $sheet->getStyle('A6')->applyFromArray($SA_p_left_center_NO_NO_NO);

        $sheet->setCellValue('C6', $dataXLSX['DYNAMIC']['HEADER_BLOCK']['STATE_NUMBER']);
        $sheet->getStyle('C6')->applyFromArray($SA_p_left_center_NO_bold_NO);
        $sheet->mergeCells('C6:H6');

        # строка 7
        $sheet->setCellValue('A7', $dataXLSX['STATIC']['HEADER_BLOCK']['HEADER_VIN']);
        $sheet->getStyle('A7')->applyFromArray($SA_p_left_center_NO_NO_NO);

        $sheet->setCellValue('C7', $dataXLSX['DYNAMIC']['HEADER_BLOCK']['VIN']);
        $sheet->getStyle('C7')->applyFromArray($SA_p_left_center_NO_bold_NO);
        $sheet->mergeCells('C7:H7');

        # строка 8
        $sheet->setCellValue('A8', $dataXLSX['STATIC']['HEADER_BLOCK']['HEADER_BODY_CHASSIS_NUMBER']);
        $sheet->getStyle('A8')->applyFromArray($SA_p_left_center_NO_NO_NO);

        # строка 9
        $sheet->setCellValue('A9', $dataXLSX['STATIC']['HEADER_BLOCK']['HEADER_BODY_COLOR']);
        $sheet->getStyle('A9')->applyFromArray($SA_p_left_center_NO_NO_NO);

        # строка 10
        $sheet->setCellValue('A10', $dataXLSX['STATIC']['HEADER_BLOCK']['HEADER_TECH_PASSPORT']);
        $sheet->getStyle('A10')->applyFromArray($SA_p_left_center_NO_NO_NO);

        # строка 11
        $sheet->setCellValue('A11', $dataXLSX['STATIC']['HEADER_BLOCK']['HEADER_OWNED_BY']);
        $sheet->getStyle('A11')->applyFromArray($SA_p_left_center_NO_NO_NO);

        # строка 12
        $sheet->setCellValue('A12', $dataXLSX['STATIC']['HEADER_BLOCK']['HEADER_CONFIDANT']);
        $sheet->getStyle('A12')->applyFromArray($SA_p_left_center_NO_NO_NO);

        /* БЛОК - работы */
        # заголовок
        $sheet->setCellValue('B14', $dataXLSX['STATIC']['WORKS_BLOCK']['WORKS_TOTAL_PAINTING_AND_REPAIR_WORKS']);
        $sheet->getStyle('B14')->applyFromArray($SA_h1_left_bottom_NO_bold_NO);
        $sheet->mergeCells('B14:H14');

        # таблица
        $sheet->setCellValue('A15', $dataXLSX['STATIC']['WORKS_BLOCK']['WORKS_TH_NUMBER_PROGRAM']);
        $sheet->getStyle('A15')->applyFromArray($SA_p_center_center_NO_bold_all)->getAlignment()->setWrapText(true);

        $sheet->setCellValue('B15', $dataXLSX['STATIC']['WORKS_BLOCK']['WORKS_TH_WORK_NAME']);
        $sheet->getStyle('B15')->applyFromArray($SA_p_center_center_NO_bold_all)->getAlignment()->setWrapText(true);

        $sheet->setCellValue('C15', $dataXLSX['STATIC']['WORKS_BLOCK']['WORKS_TH_PRICE_NH']);
        $sheet->getStyle('C15')->applyFromArray($SA_p_center_center_NO_bold_all)->getAlignment()->setWrapText(true);
        $sheet->mergeCells('C15:D15');

        $sheet->setCellValue('E15', $dataXLSX['STATIC']['WORKS_BLOCK']['WORKS_TH_TIME_NORM']);
        $sheet->getStyle('E15')->applyFromArray($SA_p_center_center_NO_bold_all)->getAlignment()->setWrapText(true);

        $sheet->setCellValue('F15', $dataXLSX['STATIC']['WORKS_BLOCK']['WORKS_TH_COUNT']);
        $sheet->getStyle('F15')->applyFromArray($SA_p_center_center_NO_bold_all)->getAlignment()->setWrapText(true);

        $sheet->setCellValue('G15', $dataXLSX['STATIC']['WORKS_BLOCK']['WORKS_TH_SUM']);
        $sheet->getStyle('G15')->applyFromArray($SA_p_center_center_NO_bold_all)->getAlignment()->setWrapText(true);
        $sheet->mergeCells('G15:H15');

        // spreadsheet row index
        $RI = 16;
        // current table index
        $TI = 1;

        foreach ($dataXLSX['DYNAMIC']['WORKS_BLOCK']['WORKS'] as $idWork => $arWork) {
            foreach ($arWork as $key => $value) {
                $sheet->setCellValue('A' . $RI, $TI);
                $sheet->getStyle('A' . $RI)->applyFromArray($SA_p_center_center_NO_NO_all)->getAlignment()->setWrapText(true);

                switch ($key) {
                    case 'NAME':

                        $sheet->setCellValue('B' . $RI, $value);
                        $sheet->getStyle('B' . $RI)->applyFromArray($SA_h1_left_bottom_NO_NO_all)->getAlignment()->setWrapText(true);

                        break;
                    case 'PRICE':

                        $sheet->setCellValue('C' . $RI, $value);
                        $sheet->getStyle('C' . $RI)->applyFromArray($SA_p_center_center_NO_NO_all)->getAlignment()->setWrapText(true);
                        $sheet->getStyle('C' . $RI)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
                        $sheet->mergeCells('C' . $RI . ':D' . $RI . '');

                        break;
                    case 'NH':

                        $sheet->setCellValue('E' . $RI, $value);
                        $sheet->getStyle('E' . $RI)->applyFromArray($SA_p_center_center_NO_NO_all)->getAlignment()->setWrapText(true);

                        break;
                    case 'COUNT':

                        $sheet->setCellValue('F' . $RI, $value);
                        $sheet->getStyle('F' . $RI)->applyFromArray($SA_p_center_center_NO_NO_all)->getAlignment()->setWrapText(true);

                        break;
                    case 'SUM':

                        $sheet->setCellValue('G' . $RI, $value);
                        $sheet->getStyle('G' . $RI)->applyFromArray($SA_p_right_center_NO_NO_all)->getAlignment()->setWrapText(true);
                        $sheet->getStyle('G' . $RI)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
                        $sheet->mergeCells('G' . $RI . ':H' . $RI . '');

                        break;
                }
            }
            $RI = $RI + 1;
            $TI = $TI + 1;
        }

        $RI = $RI - 1;

        # итого
        $sheet->setCellValue('A' . $RI, $dataXLSX['STATIC']['WORKS_BLOCK']['WORKS_TOTAL_PRICE_PAINTING_AND_REPAIR']);
        $sheet->getStyle('A' . $RI)->applyFromArray($SA_p_right_center_NO_bold_all)->getAlignment()->setWrapText(true);
        $sheet->mergeCells('A' . $RI . ':B' . $RI . '');

        $sheet->getStyle('C' . $RI)->applyFromArray($SA_NO_NO_NO_NO_NO_all)->getAlignment()->setWrapText(true);
        $sheet->mergeCells('C' . $RI . ':G' . $RI . '');

        $sheet->setCellValue('H' . $RI, $dataXLSX['DYNAMIC']['WORKS_BLOCK']['TOTAL_SUM_WORKS']);
        $sheet->getStyle('H' . $RI)->applyFromArray($SA_p_right_center_NO_bold_all)->getAlignment()->setWrapText(true);
        $sheet->getStyle('H' . $RI)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);

        $RI = $RI + 2;

        /* БЛОК - запчасти */
        # заголовок
        $sheet->setCellValue('B' . $RI, $dataXLSX['STATIC']['PARTS_BLOCK']['PARTS_TOTAL_PAINTING_AND_REPAIR_WORKS']);
        $sheet->getStyle('B' . $RI)->applyFromArray($SA_h1_left_bottom_NO_bold_NO);
        $sheet->mergeCells('B' . $RI . ':H' . $RI . '');

        $RI = $RI + 1;

        # таблица

        $sheet->setCellValue('A' . $RI, $dataXLSX['STATIC']['PARTS_BLOCK']['PARTS_TH_CATEGORY_NUMBER']);
        $sheet->getStyle('A' . $RI)->applyFromArray($SA_p_center_center_NO_bold_all)->getAlignment()->setWrapText(true);

        $sheet->setCellValue('B' . $RI, $dataXLSX['STATIC']['PARTS_BLOCK']['PARTS_TH_NUMBER_NAME']);
        $sheet->getStyle('B' . $RI)->applyFromArray($SA_p_center_center_NO_bold_all)->getAlignment()->setWrapText(true);

        $sheet->getStyle('C' . $RI)->applyFromArray($SA_p_center_center_NO_bold_all)->getAlignment()->setWrapText(true);
        $sheet->getStyle('D' . $RI)->applyFromArray($SA_p_center_center_NO_bold_all)->getAlignment()->setWrapText(true);

        $sheet->setCellValue('E' . $RI, $dataXLSX['STATIC']['PARTS_BLOCK']['PARTS_TH_NUMBER_PRICE']);
        $sheet->getStyle('E' . $RI)->applyFromArray($SA_p_center_center_NO_bold_all)->getAlignment()->setWrapText(true);

        $sheet->setCellValue('F' . $RI, $dataXLSX['STATIC']['PARTS_BLOCK']['PARTS_TH_NUMBER_COUNT']);
        $sheet->getStyle('F' . $RI)->applyFromArray($SA_p_center_center_NO_bold_all)->getAlignment()->setWrapText(true);

        $sheet->setCellValue('G' . $RI, $dataXLSX['STATIC']['PARTS_BLOCK']['PARTS_TH_NUMBER_SUM']);
        $sheet->getStyle('G' . $RI)->applyFromArray($SA_p_center_center_NO_bold_all)->getAlignment()->setWrapText(true);

        $RI = $RI + 1;

        foreach ($dataXLSX['DYNAMIC']['PARTS_BLOCK']['PARTS'] as $idPart => $arPart) {
            foreach ($arPart as $key => $value) {
                switch ($key) {
                    case 'CATEGORY_NUMBER':

                        $sheet->setCellValue('A' . $RI, $value);
                        $sheet->getStyle('A' . $RI)->applyFromArray($SA_p_center_center_NO_NO_all)->getAlignment()->setWrapText(true);

                        break;
                    case 'NAME':

                        $sheet->setCellValue('B' . $RI, $value);
                        $sheet->getStyle('B' . $RI)->applyFromArray($SA_h1_left_bottom_NO_NO_all)->getAlignment()->setWrapText(true);

                        $sheet->getStyle('C' . $RI)->applyFromArray($SA_p_center_center_NO_NO_all)->getAlignment()->setWrapText(true);
                        $sheet->getStyle('D' . $RI)->applyFromArray($SA_p_center_center_NO_NO_all)->getAlignment()->setWrapText(true);

                        break;
                    case 'PRICE':

                        $sheet->setCellValue('E' . $RI, $value);
                        $sheet->getStyle('E' . $RI)->applyFromArray($SA_p_center_center_NO_NO_all)->getAlignment()->setWrapText(true);
                        $sheet->getStyle('E' . $RI)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);

                        break;
                    case 'COUNT':

                        $sheet->setCellValue('F' . $RI, $value);
                        $sheet->getStyle('F' . $RI)->applyFromArray($SA_p_center_center_NO_NO_all)->getAlignment()->setWrapText(true);

                        break;
                    case 'SUM':

                        $sheet->setCellValue('G' . $RI, $value);
                        $sheet->getStyle('G' . $RI)->applyFromArray($SA_p_center_center_NO_NO_all)->getAlignment()->setWrapText(true);
                        $sheet->getStyle('G' . $RI)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);

                        break;
                }
            }
            $RI = $RI + 1;
        }

        $RI = $RI - 1;

        # итого
        $sheet->setCellValue('A' . $RI, $dataXLSX['STATIC']['PARTS_BLOCK']['PARTS_TOTAL_PRICE_PAINTING_AND_REPAIR']);
        $sheet->getStyle('A' . $RI)->applyFromArray($SA_p_right_center_NO_bold_all)->getAlignment()->setWrapText(true);
        $sheet->mergeCells('A' . $RI . ':F' . $RI . '');

        $sheet->setCellValue('G' . $RI, $dataXLSX['DYNAMIC']['PARTS_BLOCK']['TOTAL_SUM_PARTS']);
        $sheet->getStyle('G' . $RI)->applyFromArray($SA_p_right_center_NO_bold_all)->getAlignment()->setWrapText(true);
        $sheet->getStyle('G' . $RI)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);

        $RI = $RI + 1;

        $sheet->setCellValue('B' . $RI, $dataXLSX['STATIC']['PARTS_BLOCK']['PARTS_TOTAL']);
        $sheet->getStyle('B' . $RI)->applyFromArray($SA_h1_center_center_NO_bold_NO)->getAlignment()->setWrapText(true);
        $sheet->mergeCells('B' . $RI . ':F' . $RI . '');

        $RI = $RI + 1;

        /* БЛОК - подвал */
        $sheet->getStyle('A' . $RI . ':H' . ($RI + 2) . '')->applyFromArray($SA_NO_NO_NO_NO_NO_all)->getAlignment()->setWrapText(true);

        $sheet->setCellValue('B' . $RI, $dataXLSX['STATIC']['FOOTER_BLOCK']['FOOTER_PRICE_ELIMINATION_OF_DEFECTS']);
        $sheet->getStyle('B' . $RI)->applyFromArray($SA_p_right_center_NO_bold_NO)->getAlignment()->setWrapText(true);
        $sheet->getStyle('B' . $RI)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
        $sheet->mergeCells('B' . $RI . ':F' . $RI . '');

        $sheet->setCellValue('G' . $RI, $dataXLSX['DYNAMIC']['FOOTER_BLOCK']['TOTAL_SUM_WORKS_AND_PARTS']);
        $sheet->getStyle('G' . $RI)->applyFromArray($SA_p_right_center_NO_bold_all)->getAlignment()->setWrapText(true);
        $sheet->getStyle('G' . $RI)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
        $sheet->mergeCells('G' . $RI . ':H' . $RI . '');

        $RI = $RI + 1;

        $sheet->setCellValue('B' . $RI, $dataXLSX['STATIC']['FOOTER_BLOCK']['FOOTER_TOTAL_TO_PAY']);
        $sheet->getStyle('B' . $RI)->applyFromArray($SA_p_right_center_NO_bold_NO)->getAlignment()->setWrapText(true);
        $sheet->mergeCells('B' . $RI . ':F' . $RI . '');

        $sheet->setCellValue('G' . $RI, $dataXLSX['DYNAMIC']['FOOTER_BLOCK']['TOTAL_SUM_WORKS_AND_PARTS']);
        $sheet->getStyle('G' . $RI)->applyFromArray($SA_p_right_center_NO_bold_all)->getAlignment()->setWrapText(true);
        $sheet->getStyle('G' . $RI)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
        $sheet->mergeCells('G' . $RI . ':H' . $RI . '');

        $RI = $RI + 1;

        $sheet->setCellValue('A' . $RI, self::sumToString($dataXLSX['DYNAMIC']['FOOTER_BLOCK']['TOTAL_SUM_WORKS_AND_PARTS']));
        $sheet->getStyle('A' . $RI)->applyFromArray($SA_p_left_center_NO_bold_all)->getAlignment()->setWrapText(true);
        $sheet->mergeCells('A' . $RI . ':D' . $RI . '');

        $sheet->setCellValue('E' . $RI, $dataXLSX['STATIC']['FOOTER_BLOCK']['FOOTER_NDS_SUM']);
        $sheet->getStyle('E' . $RI)->applyFromArray($SA_p_right_center_NO_bold_NO)->getAlignment()->setWrapText(true);
        $sheet->mergeCells('E' . $RI . ':F' . $RI . '');

        $sheet->setCellValue('G' . $RI, $dataXLSX['DYNAMIC']['FOOTER_BLOCK']['NDS_SUM']);
        $sheet->getStyle('G' . $RI)->applyFromArray($SA_p_right_center_NO_bold_all)->getAlignment()->setWrapText(true);
        $sheet->getStyle('G' . $RI)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
        $sheet->mergeCells('G' . $RI . ':H' . $RI . '');

        $RI = $RI + 1;

        # подпись
        $sheet->setCellValue('A' . $RI, $dataXLSX['STATIC']['FOOTER_BLOCK']['FOOTER_COMMON_WORKS_DESCRIPTION']);
        $sheet->getStyle('A' . $RI)->applyFromArray($SA_p_left_center_NO_NO_NO)->getAlignment()->setWrapText(true);
        $sheet->mergeCells('A' . $RI . ':D' . $RI . '');

        $RI = $RI + 2;

        $sheet->setCellValue('B' . $RI, $dataXLSX['STATIC']['FOOTER_BLOCK']['FOOTER_MASTER']);
        $sheet->getStyle('B' . $RI)->applyFromArray($SA_p_right_center_NO_NO_NO)->getAlignment()->setWrapText(true);

        $sheet->getStyle('C' . $RI . ':G' . $RI . '')->applyFromArray($SA_NO_NO_NO_NO_NO_bottom)->getAlignment();

        $sheet->setCellValue('F' . $RI, $dataXLSX['DYNAMIC']['FOOTER_BLOCK']['MASTER_SHORT_NAME']);

        // Вставка значений в ячейки таблицы - END
        ###############################################################################################################
        // Создаем файл и сохраняем его - START

        global $USER;

        $fileName = 'works_parts_' . $USER->getId() . '_export.xlsx';
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/local/components/sladcovich/alphaautob24.worksparts.export.xlsx/export/';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath . $fileName);

        // Создаем файл и сохраняем его - END
        ###############################################################################################################

        return $fileName;
    }

    /**
     * Создание итогового XLSX файла с данными
     *
     * @param $dealId
     * @return string
     */
    public function createDocumentXLSXAction($dealId)
    {
        return self::generatePatternDocumentXLSX(self::getDataForDocumentXLSX($dealId));
    }
}