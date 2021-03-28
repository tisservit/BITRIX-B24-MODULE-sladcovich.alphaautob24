<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use \Bitrix\Main\Engine\Contract\Controllerable;
use \Bitrix\Main\Loader;

use \Sladcovich\Alphaautob24\Entity\ORM\PartTable;

Loader::includeModule('sladcovich.alphaautob24');

class Alphaautob24PartComponent extends CBitrixComponent implements Controllerable
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
            'getAllPartsByDealId' => ['getAllPartsByDealId' => []],
            'addPart' => ['addPart' => []],
            'deletePart' => ['deletePart' => []],
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
        $this->arResult['PARTS'] = $this->getAllPartsByDealIdAction($this->arResult['DEAL_ID']);

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
    public function getAllPartsByDealIdAction($dealId)
    {
        $allParts = [];

        $res = PartTable::getList([
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
     * @param $partCategoryNumber
     * @param $partName
     * @param $partPrice
     * @param $partCoefficient
     * @param $partCount
     * @param $partSum
     * @param $partDealB24Id
     * @return array|int
     */
    public function addPartAction($partCategoryNumber, $partName, $partPrice, $partCoefficient, $partCount, $partSum ,$partDealB24Id)
    {
        $res = PartTable::add([
            'CATEGORY_NUMBER' => $partCategoryNumber,
            'NAME' => $partName,
            'PRICE' => round($partPrice, 2),
            'COEFFICIENT' => round($partCoefficient, 2),
            'COUNT' => round($partCount, 2),
            'SUM' => round($partSum, 2),
            'DEAL_B24_ID' => $partDealB24Id,
        ]);

        if ($res->isSuccess())
        {
            return $res->getId();
        }
    }

    /**
     * Удаляем запчасть из БД
     *
     * @param $partId
     * @return \Bitrix\Main\ORM\Data\Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function deletePartAction($partId)
    {
        $part = PartTable::getByPrimary($partId)->fetchObject();

        return $part->delete();
    }
}