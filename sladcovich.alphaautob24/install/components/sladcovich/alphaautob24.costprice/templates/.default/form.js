$(document).ready(function () {

    // Общие переменные
    let dealId = $('#sladcovich-alphaautob24-costprice-dealb24id').val();

    // Общие функции
    function rewriteNumeration() {
        $('.costprice-numeration-table-js').each(function (index) {
            $(this).text(index + 1);
        });
    }

    // Table - добавление строки
    $('#sladcovich-alphaautob24-costprice_form').on('submit', function (e) {
        e.preventDefault();

        let ppNumber = $('#sladcovich-alphaautob24-costprice_pp_number').val();
        let ppDate = $('#sladcovich-alphaautob24-costprice_pp_date').val();
        let sum = $('#sladcovich-alphaautob24-costprice_sum').val();
        let note = $('#sladcovich-alphaautob24-costprice_note').val();

        let costPriceId = 0;

        // Здесь должен происходить запрос в компонент на создание элемента costprice, затем в ответ получить id созданного
        // элемента, и использовать его для создания кнопок в интерфейсе, чтобы можно было обрабатывать однозначно
        // нужные элементы в таблице интерфейса и в таблце БД
        BX.ajax.runComponentAction('sladcovich:alphaautob24.costprice', 'addCostPrice', {
            mode: 'class', // это означает, что мы хотим вызывать действие из class.php
            data: {
                costPricePPNumber: ppNumber,
                costPricePPDate: ppDate,
                costPriceSum: sum,
                costPriceNote: note,
                costPriceDealB24Id: dealId,
            },
        }).then(function (response) {
            // success
            costPriceId = response.data;
            $('#sladcovich-alphaautob24-costprice_form')[0].reset();

            let tableItems = $('#sladcovich-alphaautob24-costprice_table-items');

            tableItems.append('<tr>' +
                '<td class="costprice-numeration-table-js">' + '' + '</td>' +
                '<td>' + ppNumber + '</td>' +
                '<td>' + ppDate + '</td>' +
                '<td>' + sum + ' ₽</td>' +
                '<td>' + note + '</td>' +
                '<td>' + '<button data-id="' + costPriceId + '" data-role="costprice-table-remove" type="button" class="btn btn-danger" style="padding: 0px 10px 0px 10px"><i class="fa fa-remove" style="font-size:24px"></i></button>' + '</td>' +
                '</tr>');
            rewriteNumeration();
        }, function (response) {
            // error
            console.log('SLADCOVICH - START');
            console.log(response);
            console.log('SLADCOVICH - END');
        });
    })

    // Table - удаление строки
    $(document).on('click', 'button[data-role="costprice-table-remove"]', function (e) {
        let thisEl = $(this);
        BX.ajax.runComponentAction('sladcovich:alphaautob24.costprice', 'deleteCostPrice', {
            mode: 'class', // это означает, что мы хотим вызывать действие из class.php
            data: {
                costPriceId: $(this).data('id'),
            },
        }).then(function (response) {
            // success
            thisEl.parent().parent().remove();
            rewriteNumeration();
        }, function (response) {
            // error
            console.log('SLADCOVICH - START');
            console.log(response);
            console.log('SLADCOVICH - END');
        });
    });

});