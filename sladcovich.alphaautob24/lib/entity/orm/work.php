<?php

namespace Sladcovich\Alphaautob24\Entity\ORM;

use Bitrix\Main\Entity;
use Bitrix\Main\Loader;

Loader::includeModule('crm');

/**
 * @package Sladcovich\Alphaautob24
 */
class WorkTable extends Entity\DataManager
{
    /**
     * Return name of table
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_sladcovich_alphaautob24_entity_orm_work';
    }

    /**
     *
     * @see http://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=4803&LESSON_PATH=3913.5062.5748.4803
     * @return array
     * @throws \Bitrix\Main\SystemException
     */
    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID',               ['primary'=> true, 'autocomplete' => true]),

            new Entity\StringField('NAME',              ['title' => 'Наименование']),
            new Entity\FloatField('PRICE',              ['title' => 'Цена']),
            new Entity\FloatField('NH',                 ['title' => 'Нормочас']),
            new Entity\FloatField('COUNT',              ['title' => 'Количество']),
            new Entity\FloatField('SUM',                ['title' => 'Сумма']),

            new Entity\IntegerField('DEAL_B24_ID',      ['title'=> 'ID сделки (Битрикс 24)']),
            new Entity\ReferenceField('DEAL_B24', 'Bitrix\Crm\DealTable', ['=this.DEAL_ID_B24' => 'ref.ID']),

        ];
    }
}