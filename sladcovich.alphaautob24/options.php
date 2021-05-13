<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

/** @global $APPLICATION */
/** @global $USER */

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

Loc::loadMessages($context->getServer()->getDocumentRoot() . '/bitrix/modules/main/options.php');
Loc::loadMessages(__FILE__);

$tabControl = new CAdminTabControl('tabControl', [
    [
        'DIV' => 'common_settings',
        'TAB' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_MODULE_TAB_SALARY_TITLE'),
        'TITLE' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_MODULE_TAB_SALARY_TITLE'),
    ],
]);

if ((!empty($save) || !empty($restore)) && $request->isPost() && check_bitrix_sessid()) {

    if (!empty($restore)) {

        Option::delete('sladcovich.alphaautob24');
        CAdminMessage::showMessage([
            'MESSAGE' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_MODULE_DEFAULT_OPTIONS_RESTORED'),
            'TYPE' => 'OK',
        ]);

    } else {

        Option::set(
            'sladcovich.alphaautob24',
            'MOSCOW_MANAGERS_PERCENT',
            $request->getPost('MOSCOW_MANAGERS_PERCENT')
        );
        Option::set(
            'sladcovich.alphaautob24',
            'REGION_MANAGERS_PERCENT',
            $request->getPost('REGION_MANAGERS_PERCENT')
        );
        Option::set(
            'sladcovich.alphaautob24',
            'PARTS_PERCENT',
            $request->getPost('PARTS_PERCENT')
        );
        Option::set(
            'sladcovich.alphaautob24',
            'EXPERTS_PERCENT',
            $request->getPost('EXPERTS_PERCENT')
        );
        Option::set(
            'sladcovich.alphaautob24',
            'WORKERS_PERCENT',
            $request->getPost('WORKERS_PERCENT')
        );

        CAdminMessage::showMessage([
            'MESSAGE' => Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_MODULE_OPTIONS_SAVED'),
            'TYPE' => 'OK',
        ]);

    }
}
?>

<?
$tabControl->begin();
?>

<form method="post" action="<?= sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID) ?>">

<?
echo bitrix_sessid_post();
$tabControl->beginNextTab();
?>

    <tr>

        <td>
            <label for="MOSCOW_MANAGERS_PERCENT"><?= Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_MODULE_MOSCOW_MANAGERS_PERCENT') ?>:</label>
        <td>

        <td>
            <input type="number"
                   size="30"
                   name="MOSCOW_MANAGERS_PERCENT"
                   value="<?= Option::get('sladcovich.alphaautob24', 'MOSCOW_MANAGERS_PERCENT'); ?>"
            />
        </td>

    </tr>

    <tr>

        <td>
            <label for="REGION_MANAGERS_PERCENT"><?= Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_MODULE_REGION_MANAGERS_PERCENT') ?>:</label>
        <td>

        <td>
            <input type="number"
                   size="30"
                   name="REGION_MANAGERS_PERCENT"
                   value="<?= Option::get('sladcovich.alphaautob24', 'REGION_MANAGERS_PERCENT'); ?>"
            />
        </td>

    </tr>

    <tr>

        <td>
            <label for="PARTS_PERCENT"><?= Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_MODULE_PARTS_PERCENT') ?>:</label>
        <td>

        <td>
            <input type="number"
                   size="30"
                   name="PARTS_PERCENT"
                   value="<?= Option::get('sladcovich.alphaautob24', 'PARTS_PERCENT'); ?>"
            />
        </td>

    </tr>

    <tr>

        <td>
            <label for="EXPERTS_PERCENT"><?= Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_MODULE_EXPERTS_PERCENT') ?>:</label>
        <td>

        <td>
            <input type="number"
                   size="30"
                   name="EXPERTS_PERCENT"
                   value="<?= Option::get('sladcovich.alphaautob24', 'EXPERTS_PERCENT'); ?>"
            />
        </td>

    </tr>

    <tr>

        <td>
            <label for="WORKERS_PERCENT"><?= Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_MODULE_WORKERS_PERCENT') ?>:</label>
        <td>

        <td>
            <input type="number"
                   size="30"
                   name="WORKERS_PERCENT"
                   value="<?= Option::get('sladcovich.alphaautob24', 'WORKERS_PERCENT'); ?>"
            />
        </td>

    </tr>


<?php
$tabControl->buttons();
?>

    <input type="submit"
           name="save"
           value="<?= Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_MODULE_MAIN_SAVE') ?>"
           class="adm-btn-save"
    />
    <input type="submit"
           name="restore"
           onclick="return confirm('<?= AddSlashes(GetMessage('SLADCOVICH_ALPHAAUTOB24_MODULE_MAIN_HINT_RESTORE_DEFAULTS_WARNING')) ?>')"
           value="<?= Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_MODULE_MAIN_RESTORE_DEFAULTS') ?>"
    />

<?php
$tabControl->end();
?>

</form>
