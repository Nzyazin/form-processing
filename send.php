<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/amo/create_lead.php';
error_reporting(E_ERROR | E_PARSE);
$exceptionsLog = [];

function isLogHasErrors()
{
    global $exceptionsLog;
    foreach ($exceptionsLog as $record) foreach ($record as $status => $message) if ($status !== "Warning") return true;
    return false;
}

function sendExceptionsLog($emails)
{
    $subject = isLogHasErrors() ? "Лог кошмарных ошибок gulp-landing: 252.суперкухни.рф" : "Лог ошибок gulp-landing: 252.суперкухни.рф";
    $headers  = "From: superkuhni.rf <no-reply@xn--e1agfmhheqeu.xn--p1ai>\r\n";
    $headers .= "Reply-To: superkuhni.rf <no-reply@xn--e1agfmhheqeu.xn--p1ai>\r\n";
    $headers .= "Cc: superkuhni.rf <no-reply@xn--e1agfmhheqeu.xn--p1ai>\r\n";
    $headers .= "X-Sender: superkuhni.rf <no-reply@xn--e1agfmhheqeu.xn--p1ai>\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
    $headers .= "Date: " . date('r') . "\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= "Return-Path: superkuhni.rf <no-reply@xn--e1agfmhheqeu.xn--p1ai>\r\n"; // Return path for errors
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";

    global $exceptionsLog;
    $content = "Сайт https://252.xn--e1agfmhheqeu.xn--p1ai \r\n";
    $idx = 0;
    foreach ($exceptionsLog as $record) foreach ($record as $status => $message) $content .= $idx++ . ": $status $message\r\n";
    try {
        if (isLogHasErrors()) $content .= "Заявка: \r\n" . serialize($_POST) . "\r\n";
    } catch (\Throwable $th) {
        $content .= "Заявка: \r\nУтеряна. Ошибка обработчика ошибок\r\n";
    }

    try {
        foreach ($emails as $email) {
            try {
                if (!mail($email, $subject, $content, $headers, '-f no-reply@xn--e1agfmhheqeu.xn--p1ai')) logError(__FUNCTION__, __LINE__, "Ошибка отправки логов на почту $email");
            } catch (Exception $e) {
                logError(__FUNCTION__, __LINE__, "Ошибка отправки логов на почту $email");
            }
        }
    } catch (Exception $e) {
        logError(__FUNCTION__, __LINE__, "Ошибка отправки логов на почту");
    }
}
function filterPhone($phone)
{
    $phone = str_replace(array(' ',), '', $phone);
    if (strlen($phone) >= 12) {
        $first = mb_substr($phone, 0, 1);
        if ($first == '7' || $first == '8') {
            $phone = mb_substr($phone, 1);
        }
    }
    return $phone;
}
function logError($f, $l, $message)
{
    global $exceptionsLog;
    $exceptionsLog[] = ["Error" => ">line $l; Func $f< $message"];
}

function filterMail($mail)
{
    if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        return $mail;
    } else {
        return '$mail не валиден';
    }
}

function filterPrice($price)
{
    if (is_numeric($price) && $price >= 0) {
        return $price;
    } else {
        return '$price не верен';
    }
}

function parseCurrencyString($currencyString)
{
    // Заменяем запятые на точки и убираем все символы, кроме цифр и точек
    $numericValue = floatval(str_replace(',', '.', preg_replace('/[^\d.]/', '', $currencyString)));

    // Проверяем, является ли результат числом
    if (!is_nan($numericValue)) {
        return $numericValue;
    } else {
        trigger_error('Невозможно преобразовать строку в число: ' . $currencyString, E_USER_WARNING);
        return null;
    }
}


function validateName($name)
{
    // Удаление лишних пробелов и экранирование спецсимволов
    $name = trim($name);

    // Проверка на пустое значение
    if (empty($name)) {
        return "Имя не может быть пустым.";
    }

    // Проверка на допустимые символы (буквы, пробелы, дефисы)
    if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u", $name)) {
        return "Имя содержит недопустимые символы.";
    }

    // Если имя прошло все проверки, считаем его валидным
    return $name;
}

try {
    $post = $_POST;
    $server = $_SERVER;
} catch (\Throwable $th) {
    logError(__FUNCTION__, __LINE__, "Ошибка конфигурирования приложения");
}

try {
    $timeSpent = htmlspecialchars($_POST['timeSpent']);
    $name = htmlspecialchars($_POST['name']);
    $mail = htmlspecialchars($_POST['mail']);
    $phone = htmlspecialchars($post['phone']);
    $price = htmlspecialchars($post['price']);

    $lead_data = array();

    if ((int)$timeSpent > 30) {
        $timeSpent = 1;
    } else {
        $timeSpent = 0;
    }

    $lead_data['timeSpent'] = (string)$timeSpent;
    $lead_data['name'] =  validateName($name);
    $lead_data['mail'] = filterMail($mail);
    $lead_data['phone'] = filterPhone($phone);
    $lead_data['price'] = parseCurrencyString($price);
    $amocrm = new AmoCRM();

    $amocrm->add_lead($lead_data);
} catch (\Throwable $th) {
    logError(__FUNCTION__, __LINE__, "Ошибка создания");
}

global $exceptionsLog;
if (!empty($exceptionsLog)) {
    sendExceptionsLog(["zyazin.nikita@yandex.ru"]);
}

if (isLogHasErrors($exceptionsLog)) {
    echo 'При отправке произошла ошибка';
    http_response_code(500);
}
