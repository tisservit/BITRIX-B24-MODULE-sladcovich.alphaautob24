$(document).ready(function () {

    // Общие переменные
    let dealId = $('#sladcovich-alphaautob24-worksk-dealb24id').val();

    // Общие функции
    function rewriteNumeration() {
        $('.worksk-numeration-table-js').each(function (index) {
            $(this).text(index + 1);
        });
    }

    // Table - добавление строки
    $('#sladcovich-alphaautob24-worksk_form').on('submit', function (e) {
        e.preventDefault();

        let name = $('#sladcovich-alphaautob24-worksk_name').val();
        let price = $('#sladcovich-alphaautob24-worksk_price').val();
        let nh = $('#sladcovich-alphaautob24-worksk_nh').val();
        let count = $('#sladcovich-alphaautob24-worksk_count').val();

        let workSKId = 0;

        // Здесь должен происходить запрос в компонент на создание элемента worksk, затем в ответ получить id созданного
        // элемента, и использовать его для создания кнопок в интерфейсе, чтобы можно было обрабатывать однозначно
        // нужные элементы в таблице интерфейса и в таблце БД
        BX.ajax.runComponentAction('sladcovich:alphaautob24.worksk', 'addWorkSK', {
            mode: 'class', // это означает, что мы хотим вызывать действие из class.php
            data: {
                workSKName: name,
                workSKPrice: price,
                workSKNH: nh,
                workSKCount: count,
                workSKDealB24Id: dealId,
            },
        }).then(function (response) {
            // success
            workSKId = response.data;
            $('#sladcovich-alphaautob24-worksk_form')[0].reset();

            let sum = price * count;

            let tableItems = $('#sladcovich-alphaautob24-worksk_table-items');

            tableItems.append('<tr>' +
                '<td class="worksk-numeration-table-js">' + '' + '</td>' +
                '<td>' + name + '</td>' +
                '<td>' + price + ' ₽</td>' +
                '<td>' + nh + '</td>' +
                '<td>' + count + '</td>' +
                '<td>' + sum + ' ₽</td>' +
                '<td>' + '<button data-id="' + workSKId + '" data-role="worksk-table-remove" type="button" class="btn btn-danger" style="padding: 0px 10px 0px 10px"><i class="fa fa-remove" style="font-size:24px"></i></button>' + '</td>' +
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
    $(document).on('click', 'button[data-role="worksk-table-remove"]', function (e) {
        let thisEl = $(this);
        BX.ajax.runComponentAction('sladcovich:alphaautob24.worksk', 'deleteWorkSK', {
            mode: 'class', // это означает, что мы хотим вызывать действие из class.php
            data: {
                workSKId: $(this).data('id'),
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