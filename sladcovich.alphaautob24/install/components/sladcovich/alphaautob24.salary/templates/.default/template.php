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

\Bitrix\Main\UI\Extension::load("ui.bootstrap4");

# select 2
Bitrix\Main\Page\Asset::getInstance()->addCss('/local/dist/sladcovich/select2/css/select2.min.css');
Bitrix\Main\Page\Asset::getInstance()->addJs('/local/dist/sladcovich/select2/js/select2.min.js');

?>

<div class="container-fluid">

    <div class="row">

        <div class="col-md-9 p-4" id="sladcovich-alphaautob24-salary__table">

        </div>

        <div class="col-md-3 p-4">

            <div class="form-group">
                <label for="sladcovich-alphaautob24-salary__date_from">
                    <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_DATE_FROM');?>
                </label>
                <input
                        name="sladcovich-alphaautob24-salary__date_from"
                        id="sladcovich-alphaautob24-salary__date_from"
                        type="date"
                        class="form-control"
                        required
                />
            </div>

            <div class="form-group">
                <label for="sladcovich-alphaautob24-salary__date_to">
                    <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_DATE_TO');?>
                </label>
                <input
                        name="sladcovich-alphaautob24-salary__date_to"
                        id="sladcovich-alphaautob24-salary__date_to"
                        type="date"
                        class="form-control"
                        required
                />
            </div>

            <div class="form-group">
                <label for="sladcovich-alphaautob24-salary__employee" class="ml-1">
                    <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_EMPLOYEE');?>
                </label>
                <select
                        id="sladcovich-alphaautob24-salary__employee"
                        class="form-control">
                </select>
            </div>

            <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg mt-2" style="width: 100%" id="sladcovich-alphaautob24-salary__submit">
                    <?echo Loc::getMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_SUBMIT');?>
            </button>

        </div>

    </div>

</div>

<script>
    $(document).ready(function () {

        let table = $('#sladcovich-alphaautob24-salary__table');

        // Добавляем пользователей в select2
        $('#sladcovich-alphaautob24-salary__employee').select2({
            data: <?=\Bitrix\Main\Web\Json::encode($arResult['USERS']);?>,
            language: {
                noResults: function () {
                    return '<?=GetMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_EMPLOYEE_NOT_FOUND')?>';
                }
            },
            placeholder: '<?=GetMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_EMPLOYEE_SELECT')?>',
            allowClear: true
        });

        // Получить данные для отчета
        $('#sladcovich-alphaautob24-salary__submit').on('click', function (e) {
            e.preventDefault();

            let dateFrom = $('#sladcovich-alphaautob24-salary__date_from').val();
            let dateTo = $('#sladcovich-alphaautob24-salary__date_to').val();
            let userId = $('#sladcovich-alphaautob24-salary__employee').val();

            if (dateFrom === null || dateFrom === undefined || dateFrom.length === 0) {
                alert('<?= GetMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_DATE_FROM_ERROR')?>');
                return;
            }

            if (dateTo === null || dateTo === undefined || dateTo.length === 0) {
                alert('<?= GetMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_DATE_TO_ERROR')?>');
                return;
            }

            if (userId === null || userId === undefined) {
                alert('<?= GetMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_USER_ERROR')?>');
                return;
            }

            if (dateFrom > dateTo) {
                alert('<?= GetMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_TEMPLATE_DATE_ERROR')?>');
                return;
            }

            BX.ajax.runComponentAction('sladcovich:alphaautob24.salary', 'getDataReport', {
                mode: 'class', // это означает, что мы хотим вызывать действие из class.php
                data: {
                    dateFrom: dateFrom,
                    dateTo: dateTo,
                    userId: userId
                },
            }).then(function (response) {
                // success
                console.log('SLADCOVICH - ERROR - START');
                console.log(response.data);
                console.log('SLADCOVICH - ERROR - END');
                // Очищаем предыдущую результирующую таблицу если она существует
                let tableResult = $('#sladcovich-alphaautob24-salary__table_result');
                if (tableResult !== undefined) { tableResult.remove(); }
                // Добавляем результирующую таблицу
                table.append(response.data);
                // Добавляем данные в таблицу и рисуем ее
            }, function (response) {
                // error
                console.log('SLADCOVICH - START');
                console.log(response);
                console.log('SLADCOVICH - END');
            });

        })

    });
</script>