<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use \Bitrix\Main\Engine\Contract\Controllerable;
use \Bitrix\Main\Loader;

use \Sladcovich\Alphaautob24\Entity\ORM\CostPriceTable;

Loader::includeModule('sladcovich.alphaautob24');

class Alphaautob24CostPriceComponent extends CBitrixComponent implements Controllerable
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
            'getAllCostPricesByDealId' => ['getAllCostPricesByDealId' => []],
            'addCostPrice' => ['addCostPrice' => []],
            'deleteCostPrice' => ['deleteCostPrice' => []],
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
        $this->arResult['COST_PRICES'] = $this->getAllCostPricesByDealIdAction($this->arResult['DEAL_ID']);

        $this->includeComponentTemplate();
    }



    /* Экшены компонента */
    # Операции с "Себестоимость"

    /**
     * Получаем все себестоимости по id сделки
     *
     * @param $dealId
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getAllCostPricesByDealIdAction($dealId)
    {
        $allCostPrices = [];

        $res = CostPriceTable::getList([
            'select' => ['ID', 'PP_NUMBER', 'PP_DATE', 'SUM', 'NOTE'],
            'filter' => ['DEAL_B24_ID' => $dealId],
            'order' => ['ID']
        ]);
        while ($row = $res->fetch())
        {
            $allCostPrices[] = [
                'ID' => $row['ID'],
                'PP_NUMBER' => $row['PP_NUMBER'],
                'PP_DATE' => $row['PP_DATE'],
                'SUM' => $row['SUM'],
                'NOTE' => $row['NOTE'],
            ];
        }

        return $allCostPrices;
    }

        /**
     * Добавляем себестоимость в БД
     *
     * @param $costPricePPNumber
     * @param $costPricePPDate
     * @param $costPriceSum
     * @param $costPriceNote
     * @param $costPriceDealB24Id
     * @return array|int
     * @throws Exception
     */
    public function addCostPriceAction($costPricePPNumber, $costPricePPDate, $costPriceSum, $costPriceNote, $costPriceDealB24Id)
    {

        $date = Bitrix\Main\Type\DateTime::createFromPhp(new \DateTime($costPricePPDate));

        $res = CostPriceTable::add([
            'PP_NUMBER' => $costPricePPNumber,
            'PP_DATE' => $date,
            'SUM' => round($costPriceSum, 2),
            'NOTE' => $costPriceNote,
            'DEAL_B24_ID' => $costPriceDealB24Id,
        ]);

        if ($res->isSuccess())
        {
            return $res->getId();
        }
    }

    /**
     * Удаляем себестоимость из БД
     *
     * @param $costPriceId
     * @return \Bitrix\Main\ORM\Data\Result
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function deleteCostPriceAction($costPriceId)
    {
        $work = CostPriceTable::getByPrimary($costPriceId)->fetchObject();

        return $work->delete();
    }
}