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

<input id="sladcovich-alphaautob24-partsk-dealb24id" type="hidden" value="<?=($arResult['DEAL_ID'])?>">

<div class="slad-main-container container-fluid">

    <div class="row">

        <? // Таблица ?>
        <div class="col-md-8 p-4">
            <table class="table table-sm table-hover sladcovich-alphaautob24-table" style="overflow: auto">

                <?// Заголовки ?>
                <thead>
                <tr>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_NUMBER');?>
                    </th>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_CATEGORY_NUMBER');?>
                    </th>
                    <th style="width: 333px;">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_NAME');?>
                    </th>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_PRICE');?> ₽
                    </th>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_COEFFICIENT');?>
                    </th>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_COUNT');?>
                    </th>
                    <th>
                        <span data-role="sladcovich-alphaautob24-partsk-total" class="sladcovich-alphaautob24-partsk-total btn-info"><?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_TOTAL_SUM');?><?=$arResult['TOTAL_SUM']?> ₽</span>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_SUM');?> ₽
                    </th>
                    <th>
                        <? // Удаление ?>
                    </th>
                </tr>
                </thead>

                <?// Значения ?>
                <tbody id="sladcovich-alphaautob24-partsk_table-items">
                <? if(is_array($arResult['PARTS_SK']) && count($arResult['PARTS_SK']) > 0): ?>

                    <? $numeration = 1 ?>

                    <? foreach ($arResult['PARTS_SK'] as $arPartSK): ?>
                    <tr>
                        <td class="partsk-numeration-table-js"><?=$numeration?></td>
                        <td><?=$arPartSK['CATEGORY_NUMBER']?></td>
                        <td><?=$arPartSK['NAME']?></td>
                        <td><?=$arPartSK['PRICE']?> ₽</td>
                        <td><?=$arPartSK['COEFFICIENT']?></td>
                        <td><?=$arPartSK['COUNT']?></td>
                        <td><?=$arPartSK['SUM']?> ₽</td>
                        <td>
                            <button data-id="<?=$arPartSK['ID']?>" data-role="partsk-table-remove" type="button" class="btn btn-danger" style="padding: 0px 10px 0px 10px">
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
            <form role="form" id="sladcovich-alphaautob24-partsk_form">

                <? // Поле - кат. № ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-partsk_category_number">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_CATEGORY_NUMBER');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-partsk_category_number"
                            id="sladcovich-alphaautob24-partsk_category_number"
                            type="text"
                            class="form-control"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_CATEGORY_NUMBER_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // Поле - наименование ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-partsk_name">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_NAME');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-partsk_name"
                            id="sladcovich-alphaautob24-partsk_name"
                            type="text"
                            class="form-control"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_NAME_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // Поле - цена ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-partsk_price">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_PRICE');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-partsk_price"
                            id="sladcovich-alphaautob24-partsk_price"
                            type="number"
                            class="form-control"
                            step="0.01"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_PRICE_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // Поле - нормочас ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-partsk_coefficient">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_COEFFICIENT');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-partsk_coefficient"
                            id="sladcovich-alphaautob24-partsk_coefficient"
                            type="number"
                            class="form-control"
                            step="0.01"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_COEFFICIENT_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // Поле - количество ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-partsk_count">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_COUNT');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-partsk_count"
                            id="sladcovich-alphaautob24-partsk_count"
                            type="number"
                            class="form-control"
                            step="0.01"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_COUNT_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // кнопка submit ?>
                <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg" style="width: 100%" id="sladcovich-alphaautob24-partsk_submit">
                    <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_PARTSK_ADD');?>
                </button>

            </form>
        </div>

    </div>

</div>
