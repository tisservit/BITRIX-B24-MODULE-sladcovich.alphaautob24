$(document).ready(function () {

    // Общие переменные
    let dealId = $('#sladcovich-alphaautob24-partsk-dealb24id').val();

    // Общие функции
    function rewriteNumeration() {
        $('.partsk-numeration-table-js').each(function (index) {
            $(this).text(index + 1);
        });
    }

    function getAndSetNewTotalSum() {
        BX.ajax.runComponentAction('sladcovich:alphaautob24.partsk', 'getNewTotalSum', {
            mode: 'class', // это означает, что мы хотим вызывать действие из class.php
            data: {
                dealId: dealId
            },
        }).then(function (response) {
            // success
            let newTotalSum = response.data;
            $('span[data-role="sladcovich-alphaautob24-partsk-total"]').text('Итого: ' + response.data + ' ₽');
            BX.ajax.runComponentAction('sladcovich:alphaautob24.partsk', 'setNewTotalSum', {
                mode: 'class', // это означает, что мы хотим вызывать действие из class.php
                data: {
                    dealId: dealId,
                    newTotalSum: newTotalSum
                },
            }).then(function (response) {
                // success
            }, function (response) {
                // error
                console.log('SLADCOVICH - START');
                console.log(response);
                console.log('SLADCOVICH - END');
            });
        }, function (response) {
            // error
            console.log('SLADCOVICH - START');
            console.log(response);
            console.log('SLADCOVICH - END');
        });
    }

    // Table - добавление строки
    $('#sladcovich-alphaautob24-partsk_form').on('submit', function (e) {
        e.preventDefault();

        let categoryNumber = $('#sladcovich-alphaautob24-partsk_category_number').val();
        let name = $('#sladcovich-alphaautob24-partsk_name').val();
        let price = $('#sladcovich-alphaautob24-partsk_price').val();
        let coefficient = $('#sladcovich-alphaautob24-partsk_coefficient').val();
        let count = $('#sladcovich-alphaautob24-partsk_count').val();
        let sum = price * coefficient * count;

        let partSKId = 0;

        // Здесь должен происходить запрос в компонент на создание элемента partsk, затем в ответ получить id созданного
        // элемента, и использовать его для создания кнопок в интерфейсе, чтобы можно было обрабатывать однозначно
        // нужные элементы в таблице интерфейса и в таблце БД
        BX.ajax.runComponentAction('sladcovich:alphaautob24.partsk', 'addPartSK', {
            mode: 'class', // это означает, что мы хотим вызывать действие из class.php
            data: {
                partSKCategoryNumber: categoryNumber,
                partSKName: name,
                partSKPrice: price,
                partSKCoefficient: coefficient,
                partSKCount: count,
                partSKSum: sum,
                partSKDealB24Id: dealId,
            },
        }).then(function (response) {
            // success
            partSKId = response.data;
            $('#sladcovich-alphaautob24-partsk_form')[0].reset();

            let tableItems = $('#sladcovich-alphaautob24-partsk_table-items');

            tableItems.append('<tr>' +
                '<td class="partsk-numeration-table-js">' + '' + '</td>' +
                '<td>' + categoryNumber + '</td>' +
                '<td>' + name + '</td>' +
                '<td>' + price + ' ₽</td>' +
                '<td>' + coefficient + '</td>' +
                '<td>' + count + '</td>' +
                '<td>' + sum + ' ₽</td>' +
                '<td>' + '<button data-id="' + partSKId + '" data-role="partsk-table-remove" type="button" class="btn btn-danger" style="padding: 0px 10px 0px 10px"><i class="fa fa-remove" style="font-size:24px"></i></button>' + '</td>' +
                '</tr>');
            rewriteNumeration();
            getAndSetNewTotalSum();
        }, function (response) {
            // error
            console.log('SLADCOVICH - START');
            console.log(response);
            console.log('SLADCOVICH - END');
        });
    })

    // Table - удаление строки
    $(document).on('click', 'button[data-role="partsk-table-remove"]', function (e) {
        let thisEl = $(this);
        BX.ajax.runComponentAction('sladcovich:alphaautob24.partsk', 'deletePartSK', {
            mode: 'class', // это означает, что мы хотим вызывать действие из class.php
            data: {
                partSKId: $(this).data('id'),
            },
        }).then(function (response) {
            // success
            thisEl.parent().parent().remove();
            rewriteNumeration();
            getAndSetNewTotalSum();
        }, function (response) {
            // error
            console.log('SLADCOVICH - START');
            console.log(response);
            console.log('SLADCOVICH - END');
        });
    });

});