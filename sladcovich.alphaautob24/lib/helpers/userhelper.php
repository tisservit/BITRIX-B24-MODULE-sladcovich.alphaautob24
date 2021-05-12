<?php

namespace Sladcovich\Alphaautob24\Helpers;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;

use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

use Bitrix\Main\UserTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\SectionTable;

Loader::includeModule('sladcovich.alphaautob24');

class UserHelper
{
    /**
     * @var array - массив подразделений для отчета по заработонной плате
     */
    protected static $arReportDepartments = [
        'MOSCOW_MANAGERS' => 0,
        'REGION_MANAGERS' => 0,
        'PARTS' => 0,
        'EXPERTS' => 0,
        'WORKERS' => 0
    ];

    /**
     * Получаем всех пользователей в системе в массиве вида ('id пользователя' => 'Ф И О')
     *
     * @param false $forSelect2 - подготовить ли выборку для select2
     * @param false $forSalaryReport - отфильтровать ли пользователей по отчетным подразделениям
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function getAllUsers($forSelect2 = false, $forSalaryReport = false)
    {
        global $USER;

        $users = [];

        $arFilter = [];
        $arSelect = [
            'ID',
            'NAME',
            'LAST_NAME',
            'SECOND_NAME'
        ];

        if ($forSalaryReport === true)
        {
            $arSelect[] = 'UF_DEPARTMENT';
            self::detectReportDepartments();
            // Строим фильтр по подразделениям отчета
            $arFilter = [
                'LOGIC' => 'OR'
            ];
            foreach (self::$arReportDepartments as $departmentId)
            {
                $arFilter[] = [
                    'UF_DEPARTMENT' => $departmentId
                ];
            }
        }

        $res = UserTable::getList([
            'select' => $arSelect,
            'order' => ['ID'],
            'filter' => $arFilter
        ]);

        while ($row = $res->fetch()) {

            if ($forSelect2 === true)
            {
                $users[] = [
                    'id' => intval($row['ID']),
                    'text' => ($row['LAST_NAME'] . ' ' . $row['NAME'] . ' ' . $row['SECOND_NAME']),
                    'selected' => ($USER->getId() == intval($row['ID'])) ? 'true' : ''
                ];
            }

            if ($forSelect2 === false && $forSalaryReport === true)
            {
                foreach (self::$arReportDepartments as $departmentCode => $departmentId)
                {
                    if ($row['UF_DEPARTMENT'][0] === $departmentId)
                    {
                        $users[$row['ID']] = $departmentCode;
                    }
                }
            }

            if ($forSelect2 === false && $forSalaryReport === false)
            {
                $users[intval($row['ID'])] = ($row['LAST_NAME'] . ' ' . $row['NAME'] . ' ' . $row['SECOND_NAME']);
            }

        }

        return $users;
    }

    /**
     * Получаем id подразделений для отчета
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private static function detectReportDepartments()
    {
        $departmentIblockId = 0;

        // Получаем ID инфоблока подразделений
        $res = IblockTable::getList([
            'select' => ['ID'],
            'filter' => ['CODE' => 'departments']
        ]);
        while ($row = $res->fetch())
        {
            $departmentIblockId = $row['ID'];
        }

        // Получаем ID подразделений
        $res = SectionTable::getList([
            'select' => ['ID', 'CODE'],
            'filter' => ['IBLOCK_ID' => $departmentIblockId, '!CODE' => NULL,]
        ]);
        while ($row = $res->fetch())
        {
            foreach (self::$arReportDepartments as $departmentCode => $departmentId)
            {
                if ($row['CODE'] === $departmentCode)
                {
                    self::$arReportDepartments[$departmentCode] = intval($row['ID']);
                }
            }
        }
    }
}