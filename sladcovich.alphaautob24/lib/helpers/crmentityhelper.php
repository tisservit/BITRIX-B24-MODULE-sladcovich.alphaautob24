<?php

namespace Sladcovich\Alphaautob24\Helpers;

use \Bitrix\Main\Loader;

# NON USED use \Bitrix\Crm\LeadTable;
use \Bitrix\Crm\DealTable;
# NON USED use \Bitrix\Crm\ContactTable;
# NON USED use \Bitrix\Crm\CompanyTable;

use \Sladcovich\Worksheet\Helpers\UserFieldHelper;

Loader::includeModule('sladcovich.alphaautob24');
Loader::includeModule('crm');

class CrmEntityHelper
{
    /* Лид */
    # NON USED

    /* Сделка */
    /**
     * @var string - UF код поля сделки - дата закрытия
     */
    public static $deal_UF_CLOSE_DATE = 'UF_CLOSE_DATE';// 'UF_CLOSE_DATE'

    /**
     * Получаем массив данных закрытых сделок со значениями пользовательских полей
     *
     * @param $dateFrom - дата ОТ
     * @param $dateTo - дата ПО
     * @param $userId - id пользователя
     * @param $userIdField - code поле по которому фильтровать сделки id пользователя
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getClosedDeals($dateFrom, $dateTo, $userId, $userIdField)
    {
        $dateFrom = \Bitrix\Main\Type\DateTime::createFromPhp(new \DateTime($dateFrom));
        $dateTo = \Bitrix\Main\Type\DateTime::createFromPhp(new \DateTime($dateTo));

        $arClosedDeals = [];
        $orderCounter = 1;

        $res = DealTable::getList([
            'select' => [
                '*',
                'UF_CAR_BRAND',
                'UF_MODEL',
                'UF_STATE_NUMBER',
                'UF_CLOSE_DATE',
                'ID',
                $userIdField
            ],
            'filter' => [
                'LOGIC' => 'AND',
                    [
                        '>=UF_CLOSE_DATE' => ConvertDateTime($dateFrom, 'DD.MM.YYYY'),
                        $userIdField => $userId
                    ],
                    [
                        '<=UF_CLOSE_DATE' => ConvertDateTime($dateTo, 'DD.MM.YYYY'),
                        $userIdField => $userId
                    ]
            ],
            'order' => [
                'ID' => 'ASC'
            ]
        ]);
        while ($row = $res->fetch())
        {
            $arClosedDeals[$row['ID']] = [
                'ORDER_NUMBER' => $orderCounter,
                'CAR_BRAND' => $row['UF_CAR_BRAND'],
                'CAR_MODEL' => $row['UF_MODEL'],
                'STATE_NUMBER' => $row['UF_STATE_NUMBER'],
                'CLOSE_DATE' => ConvertDateTime($row['UF_CLOSE_DATE'], 'DD.MM.YYYY'),
                'SALARY' => 0
            ];

            $orderCounter++;
        }

        return $arClosedDeals;
    }

    /* Контакт */
    # NON USED

    /* Компания */
    # NON USED
}