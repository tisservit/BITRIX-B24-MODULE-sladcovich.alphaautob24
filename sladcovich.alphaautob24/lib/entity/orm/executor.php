<?php

namespace Sladcovich\Alphaautob24\Entity\ORM;

use Bitrix\Main\Entity;
use Bitrix\Main\Loader;

Loader::includeModule('crm');

/**
 * @package Sladcovich\Alphaautob24
 */
class ExecutorTable extends Entity\DataManager
{
    /**
     * Return name of table
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_sladcovich_alphaautob24_entity_orm_executor';
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
            new Entity\IntegerField('ID',                   ['primary'=> true, 'autocomplete' => true]),

            new Entity\FloatField('PARTICIPATION_PERCENT',  ['title' => '% участия']),
            new Entity\IntegerField('WORK_ID',              ['title'=> 'ID работы']),

            new Entity\IntegerField('USER_B24_ID',          ['title'=> 'ID исполнителя (Битрикс 24)']),
            new Entity\ReferenceField('USER_B24', 'Bitrix\Main\UserTable', ['=this.USER_B24_ID' => 'ref.ID']),

            new Entity\IntegerField('WORK_ID',              ['title'=> 'ID работы']),
            new Entity\ReferenceField('WORK', 'Sladcovich\Alphaautob24\Entity\ORM\WorkTable', ['=this.WORK_ID' => 'ref.ID']),
        ];
    }
}