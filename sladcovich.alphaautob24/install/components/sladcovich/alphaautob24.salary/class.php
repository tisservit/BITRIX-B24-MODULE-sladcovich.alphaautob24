<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

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
     * Метод из наследуемого класса CBitrixComponent - Выполнение компонента
     *
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        $this->arResult['USERS'] = UserHelper::getAllUsers(true, true);
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
        if (count(self::$arUsersDepartmentAffiliation) === 0) {
            self::$arUsersDepartmentAffiliation = UserHelper::getAllUsers(false, true);
        }

        foreach (self::$arUsersDepartmentAffiliation as $userId => $departmentCode) {
            if (intval($userId) === intval($reportUserId)) {
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
        $moscowManagersPercent = (\COption::GetOptionInt('sladcovich.alphaautob24', 'MOSCOW_MANAGERS_PERCENT') / 100);
        $regionManagersPercent = (\COption::GetOptionInt('sladcovich.alphaautob24', 'REGION_MANAGERS_PERCENT') / 100);
        $partsPercent = (\COption::GetOptionInt('sladcovich.alphaautob24', 'PARTS_PERCENT') / 100);
        $expertsPercent = (\COption::GetOptionInt('sladcovich.alphaautob24', 'EXPERTS_PERCENT') / 100);
        $workersPercent = (\COption::GetOptionInt('sladcovich.alphaautob24', 'WORKERS_PERCENT') / 100);

        switch ($departmentCode) {
            case 'MOSCOW_MANAGERS':

                $arClosedDeals['CLOSED_DEALS_DATA'] = CrmEntityHelper::getClosedDeals($dateFrom, $dateTo, $userId, 'ASSIGNED_BY_ID');

                $totalSalarySum = 0;

                foreach ($arClosedDeals['CLOSED_DEALS_DATA'] as $delaId => $arDeal) {

                    $currentSalarySum = 0;

                    $res = WorkSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch()) {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $res = PartSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch()) {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $arClosedDeals['CLOSED_DEALS_DATA'][$delaId]['SALARY'] = ($currentSalarySum * $moscowManagersPercent);

                    $totalSalarySum = $totalSalarySum + $arClosedDeals['CLOSED_DEALS_DATA'][$delaId]['SALARY'];
                }

                $arClosedDeals['TOTAL_SALARY_SUM'] = $totalSalarySum;
                $arClosedDeals['TOTAL_DEALS_COUNT'] = count($arClosedDeals['CLOSED_DEALS_DATA']);

                return $arClosedDeals;

            case 'REGION_MANAGERS':

                $arClosedDeals['CLOSED_DEALS_DATA'] = CrmEntityHelper::getClosedDeals($dateFrom, $dateTo, $userId, 'ASSIGNED_BY_ID');

                $totalSalarySum = 0;

                foreach ($arClosedDeals['CLOSED_DEALS_DATA'] as $delaId => $arDeal) {

                    $currentSalarySum = 0;

                    $res = WorkSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch()) {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $res = PartSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch()) {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $arClosedDeals['CLOSED_DEALS_DATA'][$delaId]['SALARY'] = ($currentSalarySum * $regionManagersPercent);

                    $totalSalarySum = $totalSalarySum + $arClosedDeals['CLOSED_DEALS_DATA'][$delaId]['SALARY'];
                    $dealCount = $dealCount + 1;
                }

                $arClosedDeals['TOTAL_SALARY_SUM'] = $totalSalarySum;
                $arClosedDeals['TOTAL_DEALS_COUNT'] = count($arClosedDeals['CLOSED_DEALS_DATA']);

                return $arClosedDeals;

            case 'PARTS':

                $arClosedDeals['CLOSED_DEALS_DATA'] = CrmEntityHelper::getClosedDeals($dateFrom, $dateTo, $userId, 'UF_MANAGER_OZ');

                $totalSalarySum = 0;

                foreach ($arClosedDeals['CLOSED_DEALS_DATA'] as $delaId => $arDeal) {

                    $currentSalarySum = 0;

                    $res = WorkSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch()) {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $res = PartSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch()) {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $arClosedDeals['CLOSED_DEALS_DATA'][$delaId]['SALARY'] = ($currentSalarySum * $partsPercent);

                    $totalSalarySum = $totalSalarySum + $arClosedDeals['CLOSED_DEALS_DATA'][$delaId]['SALARY'];
                    $dealCount = $dealCount + 1;
                }

                $arClosedDeals['TOTAL_SALARY_SUM'] = $totalSalarySum;
                $arClosedDeals['TOTAL_DEALS_COUNT'] = count($arClosedDeals['CLOSED_DEALS_DATA']);

                return $arClosedDeals;

            case 'EXPERTS':

                $arClosedDeals['CLOSED_DEALS_DATA'] = CrmEntityHelper::getClosedDeals($dateFrom, $dateTo, $userId, 'UF_EXPERT');

                $totalSalarySum = 0;

                foreach ($arClosedDeals['CLOSED_DEALS_DATA'] as $delaId => $arDeal) {

                    $currentSalarySum = 0;

                    $res = WorkSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch()) {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $res = PartSKTable::getList([
                        'select' => ['SUM'],
                        'filter' => ['DEAL_B24_ID' => $delaId]
                    ]);
                    while ($row = $res->fetch()) {
                        $currentSalarySum = $currentSalarySum + $row['SUM'];
                    }

                    $arClosedDeals['CLOSED_DEALS_DATA'][$delaId]['SALARY'] = ($currentSalarySum * $expertsPercent);

                    $totalSalarySum = $totalSalarySum + $arClosedDeals['CLOSED_DEALS_DATA'][$delaId]['SALARY'];
                    $dealCount = $dealCount + 1;
                }

                $arClosedDeals['TOTAL_SALARY_SUM'] = $totalSalarySum;
                $arClosedDeals['TOTAL_DEALS_COUNT'] = count($arClosedDeals['CLOSED_DEALS_DATA']);

                return $arClosedDeals;

            case 'WORKERS':

                $arClosedDeals['CLOSED_DEALS_DATA'] = CrmEntityHelper::getClosedDeals($dateFrom, $dateTo, $userId);

                $totalSalarySum = 0;
                $arExecutorWorkDeals = [];

                foreach ($arClosedDeals['CLOSED_DEALS_DATA'] as $delaId => $arDeal) {

                    $currentSalarySum = 0;

                    $res = ExecutorTable::getList([
                        'select' => ['WORK_ID', 'PARTICIPATION_PERCENT'],
                        'filter' => ['DEAL_B24_ID' => $delaId, 'USER_B24_ID' => $userId]
                    ]);
                    while ($row = $res->fetch()) {
                        $arExecutorWorkDeals[] = $delaId;

                        $subRes = WorkTable::getList([
                            'select' => ['SUM'],
                            'filter' => ['ID' => $row['WORK_ID']]
                        ]);
                        while ($subRow = $subRes->fetch()) {
                            $currentSalarySum = $currentSalarySum + ($subRow['SUM'] * ($row['PARTICIPATION_PERCENT'] / 100));
                        }
                    }

                    $arClosedDeals['CLOSED_DEALS_DATA'][$delaId]['SALARY'] = ($currentSalarySum * $workersPercent);

                    $totalSalarySum = $totalSalarySum + $arClosedDeals['CLOSED_DEALS_DATA'][$delaId]['SALARY'];
                }

                $arClosedDeals['TOTAL_SALARY_SUM'] = $totalSalarySum;
                $arClosedDeals['TOTAL_DEALS_COUNT'] = count(array_unique($arExecutorWorkDeals));

                foreach ($arClosedDeals['CLOSED_DEALS_DATA'] as $dealId => $arDeal) {
                    if (!in_array($dealId, $arExecutorWorkDeals)) {
                        unset($arClosedDeals['CLOSED_DEALS_DATA'][$dealId]);
                    }
                }

                return $arClosedDeals;
        }
    }

    /**
     * Отрисовываем таблицу отчета
     *
     * @param $arClosedDeals
     * @return string
     */
    public static function createReportTable($arClosedDeals)
    {
        // Если нет данных
        if (count($arClosedDeals['CLOSED_DEALS_DATA']) === 0)
        {
            $table = '
            <div id="sladcovich-alphaautob24-salary__table_result">  
              
                <div class="alert alert-dismissable alert-danger">
                        ' . Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_NO_DATA') . '
                </div>
                
            </div>    
            ';

            return $table;
        }

        // Если есть данные
        $table = '

        <!-- Общая информация -->
        <div id="sladcovich-alphaautob24-salary__table_result">
        
            <div class="alert alert-dismissable alert-info">
                    ' . Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TOTAL_PART_1') . $arClosedDeals['TOTAL_DEALS_COUNT'] . Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TOTAL_PART_2') . $arClosedDeals['TOTAL_SALARY_SUM'] . ' ₽
            </div>
    
            <!-- Таблица -->
            <table class="table table-sm table-hover" style="overflow: auto">
            
                <!-- Заголовки -->
                <thead id="sladcovich-alphaautob24-salary__table_titles">
                
                    <tr>
                        <th style="width: 40px">
                            ' . Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_ORDER_NUMBER') . '
                        </th>
                        <th style="width: 160px">
                            ' . Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_CAR_BRAND') . '
                        </th>
                        <th style="width: 160px">
                            ' . Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_CAR_MODEL') . '
                        </th>
                        <th style="width: 100px">
                            ' . Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_STATE_NUMBER') . '
                        </th>
                        <th style="width: 120px">
                            ' . Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_CLOSED_DATE') . '
                        </th>
                        <th style="width: 80px">
                            ' . Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_SUM') . '
                        </th>
                    </tr>
                    
        ';

        $tableRows = '';
        $orderNumber = 1;
        foreach ($arClosedDeals['CLOSED_DEALS_DATA'] as $dealId => $arDeal)
        {
            $tableRows = $tableRows . '
                        <tr>
                            <td>
                                <a href="/crm/deal/details/'.$dealId.'/">
                                    <button class="btn btn-info" style="padding: 0px 0px 0px 0px; width: 50%;">'.$orderNumber.'</button>
                                </a>
                            </td>
                            <td>
                                '.$arDeal['CAR_BRAND'].'
                            </td>
                            <td>
                                '.$arDeal['CAR_MODEL'].'
                            </td>
                            <td>
                                '.$arDeal['STATE_NUMBER'].'
                            </td>
                            <td>
                                '.$arDeal['CLOSE_DATE'].'
                            </td>
                            <td>
                                '.$arDeal['SALARY'].'
                            </td>
                        </tr>
            ';
            $orderNumber++;
        }

        $table = $table . $tableRows;


        $table = $table . '
                
                </thead>
                    
            </table>      
        </div>    
        ';

        return $table;
    }

    /* Экшены компонента */

    public function getDataReportAction($dateFrom, $dateTo, $userId)
    {
        $departmentCode = self::checkUserDepartment($userId);
        $arClosedDeals = self::collectDataForReport($departmentCode, $dateFrom, $dateTo, $userId);

        return self::createReportTable($arClosedDeals);
    }
}