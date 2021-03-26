$(document).ready(function () {

    // Общие переменные
    let dealId = $('#sladcovich-alphaautob24-dealb24id').val();
    let popupWorkId = 0;
    let users = [];

    // Общие функции
    function rewriteNumeration() {
        $('.work-numeration-table-js').each(function (index) {
            $(this).text(index + 1);
        });

        $('.work-numeration-popup-js').each(function (index) {
            $(this).text(index + 1);
        });
    }

    function getUsers() {
        BX.ajax.runComponentAction('sladcovich:alphaautob24.work', 'getAllUsers', {
            mode: 'class', // это означает, что мы хотим вызывать действие из class.php
            data: {},
        }).then(function (response) {
            // success
            users = JSON.parse(response.data);
        }, function (response) {
            // error
            console.log('SLADCOVICH - START');
            console.log(response);
            console.log('SLADCOVICH - END');
        });
    }

    function rewriteExecutorsButtonCount(workId, number) {
        let executorsButtonCount = $('span[data-id="' + workId + '"]');
        let newCount = Number(executorsButtonCount.text()) + number;
        executorsButtonCount.text(newCount);
    }

    function controlMaxPercent() {
        let percentInput = $('#sladcovich-alphaautob24-work_popup-executor-percent');
        let containerInput = $('#sladcovich-alphaautob24-work_popup-container-input');

        let currentPercentSum = 0;

        $('.percent-popup-js').each(function () {
            currentPercentSum = currentPercentSum + Number($(this).text());
        });

        let maxPercent = (100 - currentPercentSum);
        currentPercentSum === 100 ? containerInput.css({'display':'none'}) : containerInput.css({'display':'block'});
        percentInput.attr('max', maxPercent);
        percentInput.val(maxPercent);
    }

    function inputMaskCheck() {
        $('#sladcovich-alphaautob24-work_popup-executor-percent').inputmask('', { regex: '^[1-9][0-9]?$|^100$'});
    }

    // Table - добавление строки
    $('#sladcovich-alphaautob24-work_form').on('submit', function (e) {
        e.preventDefault();

        let name = $('#sladcovich-alphaautob24-work_name').val();
        let price = $('#sladcovich-alphaautob24-work_price').val();
        let nh = $('#sladcovich-alphaautob24-work_nh').val();
        let count = $('#sladcovich-alphaautob24-work_count').val();

        let workId = 0;

        // Здесь должен происходить запрос в компонент на создание элемента work, затем в ответ получить id созданного
        // элемента, и использовать его для создания кнопок в интерфейсе, чтобы можно было обрабатывать однозначно
        // нужные элементы в таблице интерфейса и в таблце БД
        BX.ajax.runComponentAction('sladcovich:alphaautob24.work', 'addWork', {
            mode: 'class', // это означает, что мы хотим вызывать действие из class.php
            data: {
                workName: name,
                workPrice: price,
                workNH: nh,
                workCount: count,
                workDealB24Id: dealId,
            },
        }).then(function (response) {
            // success
            workId = response.data;
            $('#sladcovich-alphaautob24-work_form')[0].reset();

            let sum = price * count;
            let executors = 1;

            let tableItems = $('#sladcovich-alphaautob24-work_table-items');

            tableItems.append('<tr>' +
                '<td class="work-numeration-table-js">' + '' + '</td>' +
                '<td>' + name + '</td>' +
                '<td>' + price + ' ₽</td>' +
                '<td>' + nh + '</td>' +
                '<td>' + count + '</td>' +
                '<td>' + sum + ' ₽</td>' +
                '<td>' + '<button data-id="' + workId + '" data-role="work-table-executors" type="button" class="btn btn-warning" style="padding: 0px 10px 0px 10px"><i class="fa fa-group" style="font-size:18px"></i><span>  </span>В работе: <span data-role="work-table-executors-count" data-id="' + workId + '">0</span></button>' + '</td>' +
                '<td>' + '<button data-id="' + workId + '" data-role="work-table-remove" type="button" class="btn btn-danger" style="padding: 0px 10px 0px 10px"><i class="fa fa-remove" style="font-size:24px"></i></button>' + '</td>' +
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
    $(document).on('click', 'button[data-role="work-table-remove"]', function (e) {
        let thisEl = $(this);
        BX.ajax.runComponentAction('sladcovich:alphaautob24.work', 'deleteWork', {
            mode: 'class', // это означает, что мы хотим вызывать действие из class.php
            data: {
                workId: $(this).data('id'),
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

    // Table - кнопка строки "Исполнители"
    $(document).on('click', 'button[data-role="work-table-executors"]', function (e) {

        popupWorkId = $(this).data('id');

        // Здесь нужно загрузить выбранных исполнителей к работе и подставить к работе с учетом их % участия,
        // далее необходимо подставить в переменную контента
        let executors = [];
        let popupTbody = '';
        BX.ajax.runComponentAction('sladcovich:alphaautob24.work', 'getAllExecutorsByWorkId', {
            mode: 'class', // это означает, что мы хотим вызывать действие из class.php
            data: {
                workId: popupWorkId
            },
        }).then(function (response) {
            // success
            executors = JSON.parse(response.data);
            for (let index in executors) {
                popupTbody = popupTbody + '<tr><td class="work-numeration-popup-js"></td>' +
                    '<td>' + executors[index].EXECUTOR_FIO + '</td>' +
                    '<td class="percent-popup-js">' + executors[index].PARTICIPATION_PERCENT + '</td>' +
                    '<td><button data-id="' + executors[index].ID + '" data-role="work-popup-remove" type="button" class="btn btn-danger" style="padding: 0px 10px 0px 10px"><i class="fa fa-remove" style="font-size:24px"></i></button></td>' +
                    '</tr>';
            }
            let content = '' +
                '<div class="container-fluid" id="sladcovich-alphaautob24-work_popup-container-input">' +
                '<div class="row pb-3">' +
                '<div class="col-md-7">' +
                '<select class="js-example-basic-single" name="state" style="width: 300px">' +
                '</select>' +
                '</div>' +
                '<div class="col-md-3">' +
                '<input id="sladcovich-alphaautob24-work_popup-executor-percent" style="padding: 0px 10px 0px 10px; height: 28px; margin-left: 14px;" type="text" class="form-control" value="100">' +
                '</div>' +
                '<div class="col-md-2">' +
                '<button data-role="work-popup-add" type="button" class="btn btn-success btn-circle" style="padding: 0px 10px 0px 10px"><i class="fa fa-plus" style="font-size:24px"></i></button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="row">' +
                '<div class="col-md-12">' +
                '<table class="table">' +
                '<thead><tr><th>№</th><th>Исполнитель</th><th>% участия</th><th></th></tr></thead>' +
                '<tbody id="sladcovich-alphaautob24-work_popup-table-items">' + popupTbody + '</tbody>' +
                '</table></div></div>' +
                '';

            let popup = BX.PopupWindowManager.create("popup-message", BX('element'), {
                content: content,
                width: 600, // ширина окна
                height: 800, // высота окна
                zIndex: 100, // z-index
                closeIcon: {
                    // объект со стилями для иконки закрытия, при null - иконки не будет
                    opacity: 1
                },
                titleBar: 'Исполнители',
                closeByEsc: true, // закрытие окна по esc
                darkMode: false, // окно будет светлым или темным
                autoHide: false, // закрытие при клике вне окна
                draggable: true, // можно двигать или нет
                resizable: true, // можно ресайзить
                min_height: 333, // минимальная высота окна
                min_width: 333, // минимальная ширина окна
                lightShadow: true, // использовать светлую тень у окна
                angle: false, // появится уголок
                overlay: {
                    // объект со стилями фона
                    backgroundColor: 'black',
                    opacity: 500
                },
                buttons: [
                    /*
                    new BX.PopupWindowButton({
                        text: 'Сохранить', // текст кнопки
                        id: 'sladcovich-alphaautob24-work-popup-button-executors-save-js', // идентификатор
                        className: 'ui-btn ui-btn-success', // доп. классы
                        events: {
                            click: function () {
                                // Событие при клике на кнопку
                                // Сохранить исполнителей к работе, а также добавить кол-во исполнителей в кнопке
                                // в главной таблицу

                            }
                        }
                    }),
                    */
                    new BX.PopupWindowButton({
                        text: 'ОК',
                        id: 'sladcovich-alphaautob24-work-popup-button-executors-back-js',
                        className: 'ui-btn ui-btn-primary',
                        events: {
                            click: function () {
                                // Событие при клике на кнопку
                                popup.destroy();
                            }
                        }
                    })
                ],
                events: {
                    onPopupShow: function () {
                        // Событие при показе окна
                    },
                    onPopupClose: function () {
                        // Событие при закрытии окна
                        popup.destroy();
                    }
                }
            });

            // добавляем данные в select2
            $(".js-example-basic-single").select2({
                data: users,
                language: {
                    noResults: function () {
                        return 'Сотрудник не найден';
                    }
                }
            });

            popup.show();
            inputMaskCheck();
            controlMaxPercent();
            rewriteNumeration();
        }, function (response) {
            // error
            console.log('SLADCOVICH - START');
            console.log(response);
            console.log('SLADCOVICH - END');
        });

    });


    // Popup - добавление исполнителя
    $(document).on('click', 'button[data-role="work-popup-add"]', function (e) {
        let executorFIO = $('.js-example-basic-single :selected').text();
        let executorId = $('.js-example-basic-single :selected').val();
        let percent = $('#sladcovich-alphaautob24-work_popup-executor-percent').val();

        let popupTableItems = $('#sladcovich-alphaautob24-work_popup-table-items');

        BX.ajax.runComponentAction('sladcovich:alphaautob24.work', 'addExecutor', {
            mode: 'class', // это означает, что мы хотим вызывать действие из class.php
            data: {
                executorParticipationPercent: percent,
                executorUserB24Id: executorId,
                executorWorkId: popupWorkId,
            },
        }).then(function (response) {
            // success
            popupTableItems.append('<tr>' +
                '<td class="work-numeration-popup-js">' + '' + '</td>' +
                '<td>' + executorFIO + '</td>' +
                '<td class="percent-popup-js">' + percent + '</td>' +
                '<td>' + '<button data-id="' + response.data + '" data-role="work-popup-remove" type="button" class="btn btn-danger" style="padding: 0px 10px 0px 10px"><i class="fa fa-remove" style="font-size:24px"></i></button>' + '</td>' +
                '</tr>');
            controlMaxPercent();
            rewriteNumeration();
            rewriteExecutorsButtonCount(popupWorkId, 1);
        }, function (response) {
            // error
            console.log('SLADCOVICH - START');
            console.log(response);
            console.log('SLADCOVICH - END');
        });

    });

    // Popup - удаление исполнителя
    $(document).on('click', 'button[data-role="work-popup-remove"]', function (e) {
        let thisEl = $(this);
        let executorId = $(this).data('id');
        BX.ajax.runComponentAction('sladcovich:alphaautob24.work', 'deleteExecutor', {
            mode: 'class', // это означает, что мы хотим вызывать действие из class.php
            data: {
                executorId: executorId,
            },
        }).then(function (response) {
            // success
            thisEl.parent().parent().remove();
            rewriteNumeration();
            rewriteExecutorsButtonCount(popupWorkId, -1);
            controlMaxPercent();
        }, function (response) {
            // error
            console.log('SLADCOVICH - START');
            console.log(response);
            console.log('SLADCOVICH - END');
        });
    });


    // Автозапуск функций при загрузке старницы
    getUsers();

});