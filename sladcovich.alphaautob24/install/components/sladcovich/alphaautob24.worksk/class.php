<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use \Bitrix\Main\Engine\Contract\Controllerable;
use \Bitrix\Main\Loader;

use \Sladcovich\Alphaautob24\Entity\ORM\WorkSKTable;

Loader::includeModule('sladcovich.alphaautob24');

class Alphaautob24WorkSKComponent extends CBitrixComponent implements Controllerable
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
            'getAllWorkSKByDealId' => ['getAllWorkSKByDealId' => []],
            'addWorkSK' => ['addWorkSK' => []],
            'deleteWorkSK' => ['deleteWorkSK' => []],
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
        $this->arResult['WORKS_SK'] = $this->getAllWorksSKByDealIdAction($this->arResult['DEAL_ID']);

        $this->includeComponentTemplate();
    }



    /* Экшены компонента */
    # Операции с "Себестоимость"

    /**
     * Получаем все работы СК по id сделки
     *
     * @param $dealId
     * @return array
     */
    public function getAllWorksSKByDealIdAction($dealId)
    {
        $allWorksSK = [];

        $res = WorkSKTable::getList([
            'select' => ['ID', 'NAME', 'PRICE', 'NH', 'COUNT', 'SUM'],
            'filter' => ['DEAL_B24_ID' => $dealId],
            'order' => ['ID']
        ]);
        while ($row = $res->fetch())
        {
            $allWorksSK[] = [
                'ID' => $row['ID'],
                'NAME' => $row['NAME'],
                'PRICE' => $row['PRICE'],
                'NH' => $row['NH'],
                'COUNT' => $row['COUNT'],
                'SUM' => $row['SUM']
            ];
        }

        return $allWorksSK;
    }

    /**
     * Добавляем работу СК в БД
     *
     * @param $workSKName
     * @param $workSKPrice
     * @param $workSKNH
     * @param $workSKCount
     * @param $workSKDealB24Id
     * @return mixed
     */
    public function addWorkSKAction($workSKName, $workSKPrice, $workSKNH, $workSKCount, $workSKDealB24Id)
    {
        $res = WorkSKTable::add([
            'NAME' => $workSKName,
            'PRICE' => round($workSKPrice, 2),
            'NH' => round($workSKNH, 2),
            'COUNT' => round($workSKCount, 2),
            'SUM' => round(($workSKPrice * $workSKCount), 2),
            'DEAL_B24_ID' => $workSKDealB24Id,
        ]);

        if ($res->isSuccess())
        {
            return $res->getId();
        }
    }

    /**
     * Удаляем работу СК из БД
     *
     * @param $workSKId
     * @return mixed
     */
    public function deleteWorkSKAction($workSKId)
    {
        $workSK = WorkSKTable::getByPrimary($workSKId)->fetchObject();

        return $workSK->delete();
    }
}