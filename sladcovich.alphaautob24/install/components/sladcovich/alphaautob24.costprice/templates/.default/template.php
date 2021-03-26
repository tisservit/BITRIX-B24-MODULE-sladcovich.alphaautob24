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

//CJSCore::Init(['popup']);
?>

<input id="sladcovich-alphaautob24-costprice-dealb24id" type="hidden" value="<?=($arResult['DEAL_ID'])?>">

<div class="slad-main-container container-fluid">

    <div class="row">

        <? // Таблица ?>
        <div class="col-md-8 p-4">
            <table class="table table-sm table-hover" style="overflow: auto">

                <?// Заголовки ?>
                <thead>
                <tr>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_COST_PRICE_NUMBER');?>
                    </th>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_COST_PRICE_PP_NAME');?>
                    </th>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_COST_PRICE_PP_DATE');?> ₽
                    </th>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_COST_PRICE_SUM');?>
                    </th>
                    <th style="width: 400px;">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_COST_PRICE_NOTE');?>
                    </th>
                    <th>
                        <? // Удаление ?>
                    </th>
                </tr>
                </thead>

                <?// Значения ?>
                <tbody id="sladcovich-alphaautob24-costprice_table-items">
                <? if(is_array($arResult['COST_PRICES']) && count($arResult['COST_PRICES']) > 0): ?>

                    <? $numeration = 1 ?>

                    <? foreach ($arResult['COST_PRICES'] as $arCostPrice): ?>
                    <tr>
                        <td class="costprice-numeration-table-js"><?=$numeration?></td>
                        <td><?=$arCostPrice['PP_NAME']?></td>
                        <td><?=$arCostPrice['PP_DATE']?></td>
                        <td><?=$arCostPrice['SUM']?> ₽</td>
                        <td><?=$arCostPrice['NOTE']?></td>
                        <td>
                            <button data-id="<?=$arCostPrice['ID']?>" data-role="costprice-table-remove" type="button" class="btn btn-danger" style="padding: 0px 10px 0px 10px">
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
            <form role="form" id="sladcovich-alphaautob24-costprice_form">

                <? // Поле - номер п/п ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-costprice_pp_name">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_COST_PRICE_PP_NAME');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-costprice_pp_name"
                            id="sladcovich-alphaautob24-costprice_pp_name"
                            type="text"
                            class="form-control"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_COST_PRICE_PP_NAME_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // Поле - дата п/п ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-costprice_pp_date">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_COST_PRICE_PP_DATE');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-costprice_pp_date"
                            id="sladcovich-alphaautob24-costprice_pp_date"
                            type="date"
                            class="form-control"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_COST_PRICE_PP_DATE_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // Поле - сумма ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-costprice_sum">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_COST_PRICE_SUM');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-costprice_sum"
                            id="sladcovich-alphaautob24-costprice_sum"
                            type="number"
                            class="form-control"
                            step="0.01"
                            min="0"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_COST_PRICE_SUM_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // Поле - примечание ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-costprice_note">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_COST_PRICE_NOTE');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-costprice_note"
                            id="sladcovich-alphaautob24-costprice_note"
                            type="text"
                            class="form-control"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_COST_PRICE_NOTE_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // кнопка submit ?>
                <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg" style="width: 100%" id="sladcovich-alphaautob24-costprice_submit">
                    <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_COST_PRICE_ADD');?>
                </button>

            </form>
        </div>

    </div>

</div>
