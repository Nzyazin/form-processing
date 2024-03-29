"use strict";
 // Время, проведенное на сайте до отправки
 let startTime;

 // Функция, которая запускается при загрузке страницы
 function startTrackingTime() {
    startTime = new Date();
}

// Функция, которая отправляет форму и проверяет время
function submitForm() {
    const currentTime = new Date();
    const elapsedTime = (currentTime - startTime) / 1000; // Время в секундах
    Math.round(elapsedTime);
    // Добавляем информацию о времени в форму
    document.getElementById('timeSpent').value = elapsedTime;
    console.log(elapsedTime, "elapsedTime");
}

document.addEventListener('DOMContentLoaded', function () {    

    // Запуск отслеживания времени при загрузке страницы
    startTrackingTime();

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
        main: $('[data-role="form-send"]'),
        name: $('input[type="name"]'),
        mail: $('input[type="mail"]'),
        phone: $('input[type="tel"]'),
        price: $('input[type="price"]'),

        sendAjax(e) {
            e.preventDefault();
            let $this = $(this);
            submitForm();
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
                    alert(jqXHR.responseText);
                },
                complete(jqXHR, textStatus) { submit.prop('disabled', false); }
            })
        }

    };

    if (form.main.length > 0) {
        form.main.on('submit', form.sendAjax);
    }
});
