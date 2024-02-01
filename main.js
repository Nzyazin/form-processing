"use strict";

document.addEventListener('DOMContentLoaded', function () {
    // Проверка имени
    var nameMask = new Inputmask({
        mask: 'a{1,50}', // Разрешает вводить от 1 до 50 буквенных символов
        definitions: {
            'a': {
                validator: "[A-Za-zА-Яа-я -]", // Разрешены буквы латиницы, кириллицы, пробелы и дефисы
            }
        },
        placeholder: '', // Убирает стандартный плейсхолдер маски
        showMaskOnHover: false, // Отключает показ маски при наведении
        clearIncomplete: true // Очищает поле, если введенная строка не соответствует маске
    });
    nameMask.mask(document.getElementsByName('name'));


    // Проверка телефона
    const phone = $('input[type="tel"]'),
        mask = new Inputmask({
            mask: "+7 (999) 999-99-99",
            showMaskOnHover: false,
            clearIncomplete: false,
        });

    mask.mask(phone);

    // Проверка email
    let $input = $('#emailId');
    let cursor = $input[0].selectionStart;
    let prev = $input.val();

    $input.inputmask({
        mask: "*{1,50}[.*{1,50}][.*{1,50}]@*{1,50}.*{1,20}[.*{1,20}][.*{1,20}]",
        greedy: false,
        clearIncomplete: true,
        showMaskOnHover: false,
        definitions: {
            '*': {
                validator: "[^_@.]"
            }
        }
    }).on('keyup paste', function () {
        if (this.value && /[^_a-zA-Z0-9@\-.]/i.test(this.value)) {
            this.value = prev;
            this.setSelectionRange(cursor, cursor);
            $input.trigger('input');
        } else {
            cursor = this.selectionStart;
            prev = this.value;
        }
    });


    // Проверка цены
    var priceMaskWithCurrency = new Inputmask({
        alias: "currency",
        prefix: "₽ ", // Добавляем символ доллара с пробелом в качестве префикса
        groupSeparator: ",",
        autoGroup: true,
        digits: 2,
        digitsOptional: false,
        placeholder: "0",
        rightAlign: false
    });
    priceMaskWithCurrency.mask(document.getElementsByName('price'));

    // Отправка с формы
    const form = {
        name : $('input[type="name"]'),
        mail : $('input[type="mail"]'),
        phone : $('input[type="tel"]'),
        price : $('input[type="price"]'),


        initFirstEnterPage: function () {
            let cookie = App.getCookie("firstEnterPage");
            if (!cookie) {
                let location = decodeURI(window.location.toString());
                App.setCookie('firstEnterPage', location);
            }
            let referrerCookie = App.getCookie("firstReferrer");
            if (!referrerCookie) {
                let referrer = decodeURI(document.referrer ? document.referrer.toString() : "Прямой заход");
                App.setCookie('firstReferrer', referrer, { expires: 3600 * 24 * 30 });
            }
        },

        validateForm: function () {
            $(this).validate({
                rules: {
                    phone: {
                        required: true,
                        minlength: 5,
                        pattern: (placeNumber && placeNumber.length !== 0) ? `^\\+?${placeNumber} \\([\\d-() ]*` : '^\\+?7 \\(9[\\d-() ]*'
                    }
                },
                messages: {
                    phone: {
                        pattern: 'Некорректный номер телефона.'
                    },
                }
            });
        },
        sendAjax(e) {
            e.preventDefault();
            let $this = $(this);
            if (!$this.valid()) return false;
            let fd = new FormData(this);
            let submit = $this.find('[type="submit"]');
            submit.prop('disabled', true);           
            
            function logJqXhr(jqXHR) {
                console.log(`status-text: ${jqXHR.statusText} \ncode: ${jqXHR.status} \nresponse: ${jqXHR.responseText} \n`, 'jqXHR is...');
                console.log(jqXHR);
            }

            $.ajax({
                url: '/send.php',
                type: 'POST',
                contentType: false,
                processData: false,
                data: fd,
                async success(data, textStatus, jqXHR) {
                    logJqXhr(jqXHR);
                    let src = $this.attr('data-src');
                    setTimeout(function () { window.location.pathname = src; }, 1000);
                },
                async error(jqXHR, textStatus, errorThrown) {
                    logJqXhr(jqXHR);
                    await Promise.allSettled(promisesSet);
                    alert(jqXHR.responseText);
                },
                complete(jqXHR, textStatus) { submit.prop('disabled', false); }
            })
        }

    };

    if (form.main.length > 0) {
        form.main.each(form.validateForm);
        form.main.on('submit', form.sendAjax);
    }
});
