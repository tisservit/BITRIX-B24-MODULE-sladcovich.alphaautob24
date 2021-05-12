<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;

use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

use Sladcovich\Alphaautob24\Entity\ORM\WorkTable;
use Sladcovich\Alphaautob24\Entity\ORM\WorkSKTable;

use \Sladcovich\Alphaautob24\Entity\ORM\PartSKTable;
use \Sladcovich\Alphaautob24\Entity\ORM\PartTable;

use Sladcovich\Alphaautob24\Entity\ORM\ExecutorTable;

use Sladcovich\Alphaautob24\Helpers\UserHelper;
use Sladcovich\Alphaautob24\Helpers\CrmEntityHelper;

Loader::includeModule('sladcovich.alphaautob24');

class Alphaautob24SalaryComponent extends CBitrixComponent implements Controllerable
{
    /**
     * @var float - Коэффициент в % для менеджеров "Московский отдел
     */
    protected static $moscowManagersPercent = 0.00;

    /**
     * @var float - Коэффициент в % для менеджеров "Региональный отдел"
     */
    protected static $regionManagersPercent = 0.00;

    /**
     * @var float - Коэффициент в % для работников по запчастям
     */
    protected static $partsPercent = 0.00;

    /**
     * @var float - Коэффициент в % для экспертов
     */
    protected static $expertsPercent = 0.00;

    /**
     * @var float - Коэффициент в % для мастеров
     */
    protected static $workersPercent = 0.00;

    /**
     * @var array - Массив пользователей отчетных отделов и их принадлежность к одному из отчетных отделов
     */
    protected static $arUsersDepartmentAffiliation = [];

    /* Базовые методы компонента */

    /**
     * Метод из интерфейса Controllerable для реализации AJAX
     *
     * @return array[][]
     */
    public function configureActions()
    {
        return [
            'getDataReport' => ['getDataReport' => []],
        ];
    }

    /**
     * Метод из наследуемого класса CBitrixComponent - Обработка параметров компонента
     *
     * @param $arParams
     * @return array|void
     */
    public function onPrepareComponentParams($arParams)
    {
        if (isset($arParams['MOSCOW_MANAGERS_PERCENT']) && $arParams['MOSCOW_MANAGERS_PERCENT'] > 0) {
            self::$moscowManagersPercent = $arParams['MOSCOW_MANAGERS_PERCENT'];;
        } else {
            self::$moscowManagersPercent = 0.04;
        }

        if (isset($arParams['REGION_MANAGERS_PERCENT']) && $arParams['REGION_MANAGERS_PERCENT'] > 0) {
            self::$regionManagersPercent = $arParams['REGION_MANAGERS_PERCENT'];;
        } else {
            self::$regionManagersPercent = 0.05;
        }

        if (isset($arParams['PARTS_PERCENT']) && $arParams['PARTS_PERCENT'] > 0) {
            self::$partsPercent = $arParams['PARTS_PERCENT'];;
        } else {
            self::$partsPercent = 0.01;
        }

        if (isset($arParams['EXPERTS_PERCENT']) && $arParams['EXPERTS_PERCENT'] > 0) {
            self::$expertsPercent = $arParams['EXPERTS_PERCENT'];;
        } else {
            self::$expertsPercent = 0.01;
        }

        if (isset($arParams['WORKERS_PERCENT']) && $arParams['WORKERS_PERCENT'] > 0) {
            self::$workersPercent = $arParams['WORKERS_PERCENT'];;
        } else {
            self::$workersPercent = 100;
        }
    }

    /**
     * Метод из наследуемого класса CBitrixComponent - Выполнение компонента
     *
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        $this->arResult['USERS'] = UserHelper::getAllUsers(true,true);

        $this->includeComponentTemplate();
    }

    /* Пользовательские методы компонента */
    /**
     * Определяем по логике какого отдела производить расчет для отчета
     *
     * @param $reportUserId - id пользователя
     * @return mixed
     */
    public function checkUserDepartment($reportUserId)
    {
        if (count(self::$arUsersDepartmentAffiliation) === 0)
        {
            self::$arUsersDepartmentAffiliation = UserHelper::getAllUsers(false,true);
        }

        foreach (self::$arUsersDepartmentAffiliation as $userId => $departmentCode)
        {
            if (intval($userId) === intval($reportUserId))
            {
                return $departmentCode;
            }
        }
    }

    /**
     * Получаем данные для отчета пользователя состоящего в одном из под отчетных подразделений
     *
     * @param $departmentCode
     * @param $dateFrom
     * @param $dateTo
     * @param $userId
     */
    public function collectDataForReport($departmentCode, $dateFrom, $dateTo, $userId)
    {
        switch ($departmentCode)
        {
            case 'MOSCOW_MANAGERS':

                $arClosedDeals = CrmEntityHelper::getClosedDeals($dateFrom, $dateTo, $userId, 'ASSIGNED_BY_ID');

                $totalSalarySum = 0;
                $dealCount = 0;

                foreach ($arClosedDeals as $delaId => $arDeal)
                {

                    $currentSalarySum = 0;

                    $res = WorkSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch())
                    {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $res = PartSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch())
                    {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $arClosedDeals[$delaId]['SALARY'] = ($currentSalarySum * self::$moscowManagersPercent);

                    $totalSalarySum = $totalSalarySum + $arClosedDeals[$delaId]['SALARY'];
                    $dealCount = $dealCount + 1;
                }

                $arClosedDeals['TOTAL_SALARY_SUM'] = $totalSalarySum;
                $arClosedDeals['TOTAL_DEALS_COUNT'] = $dealCount;

                return $arClosedDeals;

            case 'REGION_MANAGERS':

                $arClosedDeals = CrmEntityHelper::getClosedDeals($dateFrom, $dateTo, $userId, 'ASSIGNED_BY_ID');

                $totalSalarySum = 0;
                $dealCount = 0;

                foreach ($arClosedDeals as $delaId => $arDeal)
                {

                    $currentSalarySum = 0;

                    $res = WorkSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch())
                    {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $res = PartSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch())
                    {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $arClosedDeals[$delaId]['SALARY'] = ($currentSalarySum * self::$regionManagersPercent);

                    $totalSalarySum = $totalSalarySum + $arClosedDeals[$delaId]['SALARY'];
                    $dealCount = $dealCount + 1;
                }

                $arClosedDeals['TOTAL_SALARY_SUM'] = $totalSalarySum;
                $arClosedDeals['TOTAL_DEALS_COUNT'] = $dealCount;

                return $arClosedDeals;

            case 'PARTS':

                $arClosedDeals = CrmEntityHelper::getClosedDeals($dateFrom, $dateTo, $userId, 'UF_MANAGER_OZ');

                $totalSalarySum = 0;
                $dealCount = 0;

                foreach ($arClosedDeals as $delaId => $arDeal)
                {

                    $currentSalarySum = 0;

                    $res = WorkSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch())
                    {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $res = PartSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch())
                    {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $arClosedDeals[$delaId]['SALARY'] = ($currentSalarySum * self::$partsPercent);

                    $totalSalarySum = $totalSalarySum + $arClosedDeals[$delaId]['SALARY'];
                    $dealCount = $dealCount + 1;
                }

                $arClosedDeals['TOTAL_SALARY_SUM'] = $totalSalarySum;
                $arClosedDeals['TOTAL_DEALS_COUNT'] = $dealCount;

                return $arClosedDeals;

            case 'EXPERTS':

                $arClosedDeals = CrmEntityHelper::getClosedDeals($dateFrom, $dateTo, $userId, 'UF_EXPERT');

                $totalSalarySum = 0;
                $dealCount = 0;

                foreach ($arClosedDeals as $delaId => $arDeal)
                {

                    $currentSalarySum = 0;

                    $res = WorkSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch())
                    {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $res = PartSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch())
                    {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $arClosedDeals[$delaId]['SALARY'] = ($currentSalarySum * self::$partsPercent);

                    $totalSalarySum = $totalSalarySum + $arClosedDeals[$delaId]['SALARY'];
                    $dealCount = $dealCount + 1;
                }

                $arClosedDeals['TOTAL_SALARY_SUM'] = $totalSalarySum;
                $arClosedDeals['TOTAL_DEALS_COUNT'] = $dealCount;

                return $arClosedDeals;

            case 'WORKERS':

                $arClosedDeals = CrmEntityHelper::getClosedDeals($dateFrom, $dateTo, $userId);

                $totalSalarySum = 0;
                $dealCount = 0;

                foreach ($arClosedDeals as $delaId => $arDeal)
                {

                    $currentSalarySum = 0;

                    

                    $arClosedDeals[$delaId]['SALARY'] = ($currentSalarySum * self::$partsPercent);

                    $totalSalarySum = $totalSalarySum + $arClosedDeals[$delaId]['SALARY'];
                    $dealCount = $dealCount + 1;
                }

                $arClosedDeals['TOTAL_SALARY_SUM'] = $totalSalarySum;
                $arClosedDeals['TOTAL_DEALS_COUNT'] = $dealCount;

                return $arClosedDeals;
        }
    }

    /* Экшены компонента */

    public function getDataReportAction($dateFrom, $dateTo, $userId)
    {
        $departmentCode = self::checkUserDepartment($userId);
        $dataReport = self::collectDataForReport($departmentCode, $dateFrom, $dateTo, $userId);

        return $dataReport;
    }
}