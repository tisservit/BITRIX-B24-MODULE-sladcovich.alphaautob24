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

<input id="sladcovich-alphaautob24-work-dealb24id" type="hidden" value="<?=($arResult['DEAL_ID'])?>">

<div class="slad-main-container container-fluid">

    <div class="row">

        <? // Таблица ?>
        <div class="col-md-8 p-4">
            <table class="table table-sm table-hover" style="overflow: auto">

                <?// Заголовки ?>
                <thead>
                <tr>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_NUMBER');?>
                    </th>
                    <th style="width: 333px;">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_NAME');?>
                    </th>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_PRICE');?> ₽
                    </th>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_NH');?>
                    </th>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_COUNT');?>
                    </th>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_SUM');?> ₽
                    </th>
                    <th>
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_EXECUTORS');?>
                    </th>
                    <th>
                        <? // Удаление ?>
                    </th>
                </tr>
                </thead>

                <?// Значения ?>
                <tbody id="sladcovich-alphaautob24-costprice_table-items">
                <? if(is_array($arResult['WORKS']) && count($arResult['WORKS']) > 0): ?>

                    <? $numeration = 1 ?>

                    <? foreach ($arResult['WORKS'] as $arWork): ?>
                    <tr>
                        <td class="work-numeration-table-js"><?=$numeration?></td>
                        <td><?=$arWork['NAME']?></td>
                        <td><?=$arWork['PRICE']?> ₽</td>
                        <td><?=$arWork['NH']?></td>
                        <td><?=$arWork['COUNT']?></td>
                        <td><?=$arWork['SUM']?> ₽</td>
                        <td>
                            <button data-id="<?=$arWork['ID']?>" data-role="work-table-executors" type="button" class="btn btn-warning" style="padding: 0px 10px 0px 10px">
                                <i class="fa fa-group" style="font-size:18px"></i><span>  </span>В работе: <span data-role="work-table-executors-count" data-id="<?=$arWork['ID']?>"><?=$arWork['EXECUTORS_COUNT']?></span>
                            </button>
                        </td>
                        <td>
                            <button data-id="<?=$arWork['ID']?>" data-role="work-table-remove" type="button" class="btn btn-danger" style="padding: 0px 10px 0px 10px">
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
            <form role="form" id="sladcovich-alphaautob24-work_form">

                <? // Поле - наименование ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-work_name">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_NAME');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-work_name"
                            id="sladcovich-alphaautob24-work_name"
                            type="text"
                            class="form-control"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_NAME_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // Поле - цена ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-work_price">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_PRICE');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-work_price"
                            id="sladcovich-alphaautob24-work_price"
                            type="number"
                            class="form-control"
                            step="0.01"
                            min="0"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_PRICE_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // Поле - нормочас ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-work_nh">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_NH');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-work_nh"
                            id="sladcovich-alphaautob24-work_nh"
                            type="number"
                            class="form-control"
                            step="0.01"
                            min="0"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_NH_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // Поле - количество ?>
                <div class="form-group">
                    <label for="sladcovich-alphaautob24-work_count">
                        <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_COUNT');?>
                    </label>
                    <input
                            name="sladcovich-alphaautob24-work_count"
                            id="sladcovich-alphaautob24-work_count"
                            type="number"
                            class="form-control"
                            step="0.01"
                            min="0"
                            placeholder="<?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_COUNT_PLACEHOLDER');?>"
                            required
                    />
                </div>

                <? // кнопка submit ?>
                <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg" style="width: 100%" id="sladcovich-alphaautob24-work_submit">
                    <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_WORK_ADD');?>
                </button>

            </form>
        </div>

    </div>

</div>
