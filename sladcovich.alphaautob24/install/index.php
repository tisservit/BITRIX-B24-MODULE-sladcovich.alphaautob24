<?php
/**
 * @global Bitrix\Main\Application|CMain $APPLICATION
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== TRUE)
{
    die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\IO\Directory;

/* next only meant to search here, or we probably not found it, first message include this lang file, and store it at Loc::$includedFiles */
Loc::loadMessages(__FILE__);

/**
 * Main class for 1C-Bitrix module
 * @link http://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=2824&LESSON_PATH=3913.4609.2824
 */
Class sladcovich_alphaautob24 extends \CModule
{
    // @FIXME: code style must be as is for this string
    // bitrix marketplace not support another code style for module id
    /** @var string */
    public $MODULE_ID;
    /** @var int */
    public $MODULE_SORT = -1; // that's means first point after main
    /** @var string */
    public $MODULE_VERSION;
    /** @var string */
    public $MODULE_VERSION_DATE;
    /** @var string */
    public $MODULE_NAME;
    /** @var string */
    public $MODULE_DESCRIPTION;
    /** @var string */
    public $MODULE_GROUP_RIGHTS;
    /** @var string */
    public $PARTNER_NAME;
    /** @var string */
    public $PARTNER_URI;

    /**
     * constructor
     */
    public function __construct()
    {
        // Set version from specific file. It's required by bitrix marketplace
        $this->setVersion();

        $this->MODULE_ID = Loc::getMessage('SLADCOVICH_MODULE_ID');
        $this->MODULE_NAME = Loc::getMessage('SLADCOVICH_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('SLADCOVICH_MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';

        // @FIXME: there is not available use D7 localisation now
        // because bitrix module update system not support d7 as localization phrases
        $this->PARTNER_NAME = GetMessage('SLADCOVICH_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = GetMessage('SLADCOVICH_MODULE_PARTNER_URL');
    }

    /**
     * Set module version
     *
     * @throws \Exception
     * @throws \Bitrix\Main\IO\FileNotFoundException
     */
    private function setVersion()
    {
        if (!file_exists(__DIR__ . "/version.php"))
        {
            throw new FileNotFoundException(__DIR__ . "/version.php");
        }
        /** @var array Should be reassigned at file version.php */
        $arModuleVersion = array();
        include(__DIR__ . "/version.php");

        if (!isset($arModuleVersion['VERSION']) || !isset($arModuleVersion['VERSION_DATE']))
        {
            throw new Exception(Loc::getMessage('CBIT_TERMINAL_MOD_E_INCORRECT_VERSION', array('#FILE_PATH' => __DIR__ . '/version.php')), 1);
        }

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
    }

    /**
     * Installation steps, and register it at system.
     *
     * @return bool
     */
    public function DoInstall()
    {
        if (!$this->InstallDB()) {
            return false; // break installation with error
        }
        if (!$this->InstallFiles()) {
            return false; // break installation with error
        }

        ModuleManager::registerModule($this->MODULE_ID); // register module in system
    }

    /**
     * Uninstall module step by step, and remove it from system.
     *
     * @return bool
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function DoUninstall()
    {

        if (!$this->UnInstallDB()) {
            return false; // break uninstallation with error
        }
        if (!$this->UnInstallFiles()) {
            return false; // break installation with error
        }

        ModuleManager::unRegisterModule($this->MODULE_ID); // register module in system
    }

    /**
     * Install tables for each used database
     *
     * @return bool
     */
    public function InstallDB()
    {
        global $DB;
        // careful we must have instruction CREATE TABLE IF NOT EXISTS for tables in sql file
        // or this rule for each table
        // if( !$DB->Query("SELECT 'x' FROM a_test WHERE 1=0", true) )
        $DB->RunSQLBatch(__DIR__."/db/".strtolower($DB->type)."/install.sql");

        return true;
    }

    /**
     * Uninstall tables for each used database
     *
     * @param bool $save Save tables and data. Do not truncate or remove tables.
     * @return bool
     */
    public function UnInstallDB()
    {
        global $DB;
        $DB->RunSQLBatch(__DIR__."/db/".strtolower($DB->type)."/uninstall.sql");

        return true;
    }

    /**
     * Install files
     *
     * @return bool
     */
    public function InstallFiles()
    {
        // Добавление css и js
        CopyDirFiles(__DIR__ . '/dist/', $_SERVER['DOCUMENT_ROOT'] . '/local/dist/sladcovich/', true, true);

        // Добавление компонентов sladcovich
        CopyDirFiles(__DIR__ . '/components/sladcovich/alphaautob24.work/', $_SERVER['DOCUMENT_ROOT'] . '/local/components/sladcovich/alphaautob24.work/', true, true);
        CopyDirFiles(__DIR__ . '/components/sladcovich/alphaautob24.worksk/', $_SERVER['DOCUMENT_ROOT'] . '/local/components/sladcovich/alphaautob24.worksk/', true, true);
        CopyDirFiles(__DIR__ . '/components/sladcovich/alphaautob24.part/', $_SERVER['DOCUMENT_ROOT'] . '/local/components/sladcovich/alphaautob24.part/', true, true);
        CopyDirFiles(__DIR__ . '/components/sladcovich/alphaautob24.partsk/', $_SERVER['DOCUMENT_ROOT'] . '/local/components/sladcovich/alphaautob24.partsk/', true, true);
        CopyDirFiles(__DIR__ . '/components/sladcovich/alphaautob24.costprice/', $_SERVER['DOCUMENT_ROOT'] . '/local/components/sladcovich/alphaautob24.costprice/', true, true);
        CopyDirFiles(__DIR__ . '/components/sladcovich/alphaautob24.salary/', $_SERVER['DOCUMENT_ROOT'] . '/local/components/sladcovich/alphaautob24.salary/', true, true);
        CopyDirFiles(__DIR__ . '/components/sladcovich/alphaautob24.worksparts.export.xlsx/', $_SERVER['DOCUMENT_ROOT'] . '/local/components/sladcovich/alphaautob24.worksparts.export.xlsx/', true, true);

        // todo: WARNING !!! this is rewrite default bitrix components in /local/components/bitrix/ - better find another decision in future
        // create additional tab in deal card
        CopyDirFiles(__DIR__ . '/components/bitrix/crm.deal.details/', $_SERVER['DOCUMENT_ROOT'] . '/local/components/bitrix/crm.deal.details/', true, true);

        return true;
    }

    /**
     * Uninstall files
     *
     * @return bool
     */
    public function UnInstallFiles()
    {
        // Удаление css и js
        Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/local/dist/sladcovich/');

        // Удаление компонентов sladcovich
        Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/local/components/sladcovich/alphaautob24.work/');
        Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/local/components/sladcovich/alphaautob24.worksk/');
        Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/local/components/sladcovich/alphaautob24.part/');
        Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/local/components/sladcovich/alphaautob24.partsk/');
        Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/local/components/sladcovich/alphaautob24.costprice/');
        Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/local/components/sladcovich/alphaautob24.salary/');
        Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/local/components/sladcovich/alphaautob24.worksparts.export.xlsx/');

        // todo: WARNING !!! this is delete default bitrix components in /local/components/bitrix/ - better find another decision in future
        // create additional tab in deal card
        Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/local/components/bitrix/crm.deal.details/');

        return true;
    }
}

