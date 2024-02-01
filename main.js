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
});
