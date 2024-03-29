<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use \Bitrix\Main\Engine\Contract\Controllerable;
use \Bitrix\Main\Loader;

use \Sladcovich\Alphaautob24\Entity\ORM\WorkTable;
use \Sladcovich\Alphaautob24\Entity\ORM\ExecutorTable;

Loader::includeModule('sladcovich.alphaautob24');

class Alphaautob24WorkComponent extends CBitrixComponent implements Controllerable
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
            'getAllUsers' => ['getAllUsers' => []],
            'getAllWorksByDealId' => ['getAllWorksByDealId' => []],
            'addWork' => ['addWork' => []],
            'deleteWork' => ['deleteWork' => []],
            'getAllExecutorsByWorkId' => ['getAllExecutorsByWorkId' => []],
            'addExecutor' => ['addExecutor' => []],
            'deleteExecutor' => ['deleteExecutor' => []],
            'getNewTotalSum' => ['getNewTotalSum' => []],
            'setNewTotalSum' => ['setNewTotalSum' => []],
        ];
    }

    /**
     * Метод из наследуемого класса CBitrixComponent - Выполнение компонента
     *
     * @return mixed|void|null
     */
    public function executeComponent()
    {

        $this->arResult['DEAL_ID'] = $this->arParams['DEAL_ID']['UF_DEAL_ID'];
        $this->arResult['WORKS'] = $this->getAllWorksByDealIdAction($this->arResult['DEAL_ID']);
        $this->arResult['TOTAL_SUM'] = $this->getNewTotalSumAction($this->arResult['DEAL_ID']);
        $this->includeComponentTemplate();
    }



    /* Пользовательские методы компонента */
    /**
     * Получаем полное ФИО пользователя
     *
     * @param $userId
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getFullName($userId)
    {
        $userFullName = '';

        $res = \Bitrix\Main\UserTable::getList([
            'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'],
            'filter' => ['ID' => $userId],
            'limit' => 1
        ]);

        while ($row = $res->fetch())
        {
            $userFullName = $row['LAST_NAME'].' '.$row['NAME'].' '.$row['SECOND_NAME'];
        }

        return $userFullName;

    }



    /* Экшены компонента */
    # Вспомогательные экшены

    /**
     * Получаем всех пользователей в системе
     *
     * @return mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getAllUsersAction()
    {
        global $USER;

        $users = [];

        $res = \Bitrix\Main\UserTable::getList([
            'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'],
            'order' => ['ID']
        ]);

        while ($row = $res->fetch())
        {
            $users[] = [
                'id' => intval($row['ID']),
                'text' => ($row['LAST_NAME'].' '.$row['NAME'].' '.$row['SECOND_NAME']),
                'selected' => ($USER->getId() ==  intval($row['ID'])) ? 'true' : ''
            ];
        }

        return \Bitrix\Main\Web\Json::encode($users);
    }



    # Операции с "Работа"

    /**
     * Получаем все работы по id сделки
     *
     * @param $dealId
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getAllWorksByDealIdAction($dealId)
    {
        $allWorks = [];

        $totalSum = 0;

        $res = WorkTable::getList([
            'select' => ['ID', 'NAME', 'PRICE', 'NH', 'COUNT', 'SUM'],
            'filter' => ['DEAL_B24_ID' => $dealId],
            'order' => ['ID']
        ]);
        while ($row = $res->fetch())
        {
            $allWorks[] = [
                'ID' => $row['ID'],
                'NAME' => $row['NAME'],
                'PRICE' => $row['PRICE'],
                'NH' => $row['NH'],
                'COUNT' => $row['COUNT'],
                'SUM' => $row['SUM'],
                'EXECUTORS_COUNT' => count(self::getAllExecutorsByWorkIdAction($row['ID'], false))
            ];

            $totalSum = $totalSum + $row['SUM'];
        }

        $this->arResult['TOTAL_SUM'] = $totalSum;

        return $allWorks;
    }

    /**
     * Добавляем работу в БД
     *
     * @param $workName
     * @param $workPrice
     * @param $workNH
     * @param $workCount
     * @param $workDealB24Id
     * @return array|int
     * @throws Exception
     */
    public function addWorkAction($workName, $workPrice, $workNH, $workCount, $workDealB24Id)
    {
        $res = WorkTable::add([
            'NAME' => $workName,
            'PRICE' => round($workPrice, 2),
            'NH' => round($workNH, 2),
            'COUNT' => round($workCount, 2),
            'SUM' => round(($workPrice * $workNH * $workCount), 2),
            'DEAL_B24_ID' => $workDealB24Id,
        ]);

        if ($res->isSuccess())
        {
            return $res->getId();
        }
    }

    /**
     * Удаляем работу из БД
     * Удаляем исполнителей этой работы из БД
     *
     * @param $workId
     * @return \Bitrix\Main\ORM\Data\Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function deleteWorkAction($workId)
    {
        $res = ExecutorTable::getList([
            'select' => ['ID'],
            'filter' => ['WORK_ID' => $workId],
            'order' => ['ID']
        ]);
        while ($row = $res->fetch())
        {
            ExecutorTable::getByPrimary($row['ID'])->fetchObject()->delete();
        }
        $work = WorkTable::getByPrimary($workId)->fetchObject();

        return $work->delete();
    }



    # Операции с "Исполнители"

    /**
     * Получаем всех исполнителей по id работы
     *
     * @param $workId
     * @param bool $json
     * @return array|mixed
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getAllExecutorsByWorkIdAction($workId, $json = true)
    {
        $allWorkExecutors = [];

        $res = ExecutorTable::getList([
            'select' => ['ID', 'USER_B24_ID', 'PARTICIPATION_PERCENT'],
            'filter' => ['WORK_ID' => $workId],
            'order' => ['ID']
        ]);
        while ($row = $res->fetch())
        {
            $allWorkExecutors[] = [
                'ID' => $row['ID'],
                'EXECUTOR_FIO' => self::getFullName($row['USER_B24_ID']),
                'PARTICIPATION_PERCENT' => $row['PARTICIPATION_PERCENT'],
            ];
        }

        switch ($json)
        {
            case true:
                return \Bitrix\Main\Web\Json::encode($allWorkExecutors);
            case false:
                return $allWorkExecutors;
                break;
        }
    }

    /**
     * Добавляем исполнителя работы в БД
     *
     * @param $executorParticipationPercent
     * @param $executorUserB24Id
     * @param $executorWorkId
     * @param $executorDealB24Id
     * @return mixed
     */
    public function addExecutorAction($executorParticipationPercent, $executorUserB24Id, $executorWorkId, $executorDealB24Id)
    {

        $res = ExecutorTable::add([
            'PARTICIPATION_PERCENT' => round($executorParticipationPercent, 2),
            'USER_B24_ID' => $executorUserB24Id,
            'WORK_ID' => $executorWorkId,
            'DEAL_B24_ID' => $executorDealB24Id
        ]);

        if ($res->isSuccess())
        {
            return $res->getId();
        }
    }

    /**
     * Удаляем исполнителя работы из БД
     *
     * @param $executorId
     * @return \Bitrix\Main\ORM\Data\Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function deleteExecutorAction($executorId)
    {
        $executor = ExecutorTable::getByPrimary($executorId)->fetchObject();

        return $executor->delete();
    }

    /**
     * Получаем сумму итого
     *
     * @param $dealId
     * @return int|mixed
     */
    public function getNewTotalSumAction($dealId)
    {
        $totalSum = 0;

        $res = WorkTable::getList([
            'select' => ['ID', 'SUM',],
            'filter' => ['DEAL_B24_ID' => $dealId],
            'order' => ['ID']
        ]);
        while ($row = $res->fetch())
        {
            $totalSum = $totalSum + $row['SUM'];
        }

        return $totalSum;
    }

    /**
     * Указываем сумму итого в пользовательское поле
     * @param $dealId
     * @param $newTotalSum
     */
    public function setNewTotalSumAction($dealId, $newTotalSum)
    {
        $GLOBALS["USER_FIELD_MANAGER"]->Update("CRM_DEAL", $dealId, Array("UF_WORK_TOTAL_SUM" => $newTotalSum));
    }

    /**
     * Получаем все работы и их общую сумму
     *
     * @param $dealId
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getAllWorksByDealIdForXLSXDocument($dealId)
    {
        $allWorks = [];
        $totalSum = 0;

        $res = WorkTable::getList([
            'select' => ['ID', 'NAME', 'PRICE', 'NH', 'COUNT', 'SUM'],
            'filter' => ['DEAL_B24_ID' => $dealId],
            'order' => ['ID']
        ]);
        while ($row = $res->fetch())
        {
            $allWorks[$row['ID']] = [
                'NAME' => $row['NAME'],
                'PRICE' => $row['PRICE'],
                'NH' => $row['NH'],
                'COUNT' => $row['COUNT'],
                'SUM' => $row['SUM']
            ];

            $totalSum = $totalSum + $row['SUM'];
        }

        $allWorks['TOTAL_WORKS_SUM'] = $totalSum;

        return $allWorks;
    }
}