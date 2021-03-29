<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Bitrix\Main\Localization\Loc;

// add css
# bs 4
Bitrix\Main\Page\Asset::getInstance()->addCss('/local/dist/sladcovich/bs_4/css/bootstrap.min.css');
Bitrix\Main\Page\Asset::getInstance()->addCss('/local/dist/sladcovich/bs_4/css/bootstrap-grid.min.css');
Bitrix\Main\Page\Asset::getInstance()->addCss('/local/dist/sladcovich/bs_4/css/bootstrap-reboot.min.css');
# font awesome
Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/css/main/font-awesome.css');
# select 2
Bitrix\Main\Page\Asset::getInstance()->addCss('/local/dist/sladcovich/select2/css/select2.min.css');



// add js
# bs 4
Bitrix\Main\Page\Asset::getInstance()->addJs('/local/dist/sladcovich/bs_4/js/jquery.min.js');
Bitrix\Main\Page\Asset::getInstance()->addJs('/local/dist/sladcovich/bs_4/js/bootstrap.min.js');
Bitrix\Main\Page\Asset::getInstance()->addJs('/local/dist/sladcovich/bs_4/js/bootstrap.bundle.min.js');
Bitrix\Main\Page\Asset::getInstance()->addJs('/local/dist/sladcovich/bs_4/js/jsDelivr.min.js');
# component js
Bitrix\Main\Page\Asset::getInstance()->addJs($templateFolder . '/form.js');
# select 2
Bitrix\Main\Page\Asset::getInstance()->addJs('/local/dist/sladcovich/select2/js/select2.min.js');
# inputmask
Bitrix\Main\Page\Asset::getInstance()->addJs('/local/dist/sladcovich/inputmask/js/jquery.inputmask.min.js');

Loc::loadMessages(__FILE__);

CJSCore::Init(['popup']);
?>

<input id="sladcovich-alphaautob24-worksk-dealb24id" type="hidden" value="<?=($arResult['DEAL_ID'])?>">

<div class="slad-main-container container-fluid">

    <div class="row">

        <? // Таблица ?>
        <div class="col-md-8 p-4">
            <table class="table table-sm table-hover sladcovich-alphaautob24-table" style="overflow: auto">

                <?// Заголовки ?>
                <thead>
                <tr>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_NUMBER');?>
                    </th>
                    <th style="width: 333px;">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_NAME');?>
                    </th>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_PRICE');?> ₽
                    </th>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_NH');?>
                    </th>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_COUNT');?>
                    </th>
                    <th>
                        <span data-role="sladcovich-alphaautob24-worksk-total" class="sladcovich-alphaautob24-worksk-total btn-info"><?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_TOTAL_SUM');?><?=$arResult['TOTAL_SUM']?> ₽</span>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_SUM');?> ₽
                    </th>
                    <th>
                        <? // Удаление ?>
                    </th>
                </tr>
                </thead>

                <?// Значения ?>
                <tbody id="sladcovich-alphaautob24-worksk_table-items">
                <? if(is_array($arResult['WORKS_SK']) && count($arResult['WORKS_SK']) > 0): ?>

                    <? $numeration = 1 ?>

                    <? foreach ($arResult['WORKS_SK'] as $arWorkSK): ?>
                    <tr>
                        <td class="worksk-numeration-table-js"><?=$numeration?></td>
                        <td><?=$arWorkSK['NAME']?></td>
                        <td><?=$arWorkSK['PRICE']?> ₽</td>
                        <td><?=$arWorkSK['NH']?></td>
                        <td><?=$arWorkSK['COUNT']?></td>
                        <td><?=$arWorkSK['SUM']?> ₽</td>
                        <td>
                            <button data-id="<?=$arWorkSK['ID']?>" data-role="worksk-table-remove" type="button" class="btn btn-danger" style="padding: 0px 10px 0px 10px">
                                <i class="fa fa-remove" style="font-size:24px"></i>
                            </button>
                        </td>
                    </tr>

                    <?$numeration+=1?>

                    <? endforeach; ?>

                <? endif; ?>
                </tbody>

            </table>
        </div>

        <? // Форма добавления ?>
        <div class="col-md-4 p-4">
            <form role="form" id="sladcovich-alphaautob24-worksk_form">

                <? // Поле - наименование ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-worksk_name">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_NAME');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-worksk_name"
                            id="sladcovich-alphaautob24-worksk_name"
                            type="text"
                            class="form-control"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_NAME_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // Поле - цена ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-worksk_price">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_PRICE');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-worksk_price"
                            id="sladcovich-alphaautob24-worksk_price"
                            type="number"
                            class="form-control"
                            step="0.01"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_PRICE_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // Поле - нормочас ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-worksk_nh">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_NH');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-worksk_nh"
                            id="sladcovich-alphaautob24-worksk_nh"
                            type="number"
                            class="form-control"
                            step="0.01"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_NH_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // Поле - количество ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-worksk_count">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_COUNT');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-worksk_count"
                            id="sladcovich-alphaautob24-worksk_count"
                            type="number"
                            class="form-control"
                            step="0.01"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_COUNT_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // кнопка submit ?>
                <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg" style="width: 100%" id="sladcovich-alphaautob24-worksk_submit">
                    <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORKSK_ADD');?>
                </button>

            </form>
        </div>

    </div>

</div>
