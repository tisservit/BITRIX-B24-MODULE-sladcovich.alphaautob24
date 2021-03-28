<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use \Bitrix\Main\Engine\Contract\Controllerable;
use \Bitrix\Main\Loader;

use \Sladcovich\Alphaautob24\Entity\ORM\PartSKTable;

Loader::includeModule('sladcovich.alphaautob24');

class Alphaautob24PartSKComponent extends CBitrixComponent implements Controllerable
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
            'getAllPartsSKByDealId' => ['getAllPartsSKByDealId' => []],
            'addPartSK' => ['addPartSK' => []],
            'deletePartSK' => ['deletePartSK' => []],
        ];
    }

    /**
     * Метод из наследуемого класса CBitrixComponent - Выполнение компонента
     *
     * @return mixed|void|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function executeComponent()
    {

        $this->arResult['DEAL_ID'] = $this->arParams['DEAL_ID']['UF_DEAL_ID'];
        $this->arResult['PARTS_SK'] = $this->getAllPartsSKByDealIdAction($this->arResult['DEAL_ID']);

        $this->includeComponentTemplate();
    }



    /* Экшены компонента */
    # Операции с "Себестоимость"

    /**
     * Получаем все запчасти по id сделки
     *
     * @param $dealId
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getAllPartsSKByDealIdAction($dealId)
    {
        $allParts = [];

        $res = PartSKTable::getList([
            'select' => ['ID', 'CATEGORY_NUMBER', 'NAME', 'PRICE', 'COEFFICIENT', 'COUNT', 'SUM'],
            'filter' => ['DEAL_B24_ID' => $dealId],
            'order' => ['ID']
        ]);
        while ($row = $res->fetch())
        {
            $allParts[] = [
                'ID' => $row['ID'],
                'CATEGORY_NUMBER' => $row['CATEGORY_NUMBER'],
                'NAME' => $row['NAME'],
                'PRICE' => $row['PRICE'],
                'COEFFICIENT' => $row['COEFFICIENT'],
                'COUNT' => $row['COUNT'],
                'SUM' => $row['SUM'],
            ];
        }

        return $allParts;
    }

    /**
     * Добавляем запчасть в БД
     *
     * @param $partSKCategoryNumber
     * @param $partSKName
     * @param $partSKPrice
     * @param $partSKCoefficient
     * @param $partSKCount
     * @param $partSKSum
     * @param $partSKDealB24Id
     * @return array|int
     */
    public function addPartSKAction($partSKCategoryNumber, $partSKName, $partSKPrice, $partSKCoefficient, $partSKCount, $partSKSum ,$partSKDealB24Id)
    {
        $res = PartSKTable::add([
            'CATEGORY_NUMBER' => $partSKCategoryNumber,
            'NAME' => $partSKName,
            'PRICE' => round($partSKPrice, 2),
            'COEFFICIENT' => round($partSKCoefficient, 2),
            'COUNT' => round($partSKCount, 2),
            'SUM' => round($partSKSum, 2),
            'DEAL_B24_ID' => $partSKDealB24Id,
        ]);

        if ($res->isSuccess())
        {
            return $res->getId();
        }
    }

    /**
     * Удаляем запчасть из БД
     *
     * @param $partSKId
     * @return \Bitrix\Main\ORM\Data\Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function deletePartSKAction($partSKId)
    {
        $partSK = PartSKTable::getByPrimary($partSKId)->fetchObject();

        return $partSK->delete();
    }
}