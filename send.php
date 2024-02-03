<?php function __autoload($aClassName)
{
    $aClassFilePath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'amocrm' . DIRECTORY_SEPARATOR . $aClassName . '.php';
    if (file_exists($aClassFilePath)) {
        require_once $aClassFilePath;
        return true;
    }
    return false;
}

function filterPhone($phone) {
    $phone = str_replace(array('(', ')', '-', '+', ' ',),'',$phone);
    if (strlen($phone)>=10) {
        $first = mb_substr($phone, 0, 1);
        if ($first == '7' || $first == '8') {
            $phone = mb_substr($phone, 1);
        }
    }
    return $phone;
} ?><?php session_start();

$post   = $_POST;
$server = $_SERVER;

if ($server['REQUEST_METHOD'] !== 'POST')
{
    header('Location: https://252.xn--e1agfmhheqeu.xn--p1ai');
    exit();
}

function addRow($name, $values) {
    $res = '<tr><td>' . $name . ': ' . '</td><td>';
    $valuesContent = '';
    foreach($values as $value) {
        $valuesContent .= $value . '<br>';
    }
    $res .= $valuesContent . '</td></tr>';
    return $res;
}

function html2ascii($s) {
    // convert links
    $s = preg_replace('/<a\s+.*? href="?([^\">]*)"?[^>]*>(.*?)<\/a>/i', '$2 ($1)', $s);

    // convert p, br, tr and hr tags
    $s = preg_replace('@<(b|h|t)r[^>]*>(?=\<)@i', "\n", $s);
    $s = preg_replace('@<p[^>]*>@i', "\n\n", $s);
    $s = preg_replace('@</td[^>]*>@i', " ", $s);
    $s = preg_replace('@<div[^>]*>(.*)(?=\<)@i', "\n" . '$1' . "\n", $s);

    // convert bold and italic tags
    $s = preg_replace('@<b[^>]*>(.*?)(?=\<)@i', '*$1*', $s);
    $s = preg_replace('@<strong[^>]*>(.*?)(?=\<)@i', '*$1*', $s);
    $s = preg_replace('@<i[^>]*>(.*?)(?=\<)@i', '_$1_', $s);
    $s = preg_replace('@<em[^>]*>(.*?)(?=\<)@i', '_$1_', $s);

    // decode any entities
    $s = strtr($s,array_flip(get_html_translation_table(HTML_ENTITIES)));

    // decode numbered entities
    $s = preg_replace_callback('/&#(\d+);/', function($matches) {
        return chr(str_replace(";", "", str_replace("&#", "",$matches[0])));
    }, $s);

    // strip any remaining HTML tags
    $s = strip_tags($s);

    // return the string
    return trim($s);
}

$content = '<html><body><table>';
$subject = '';
$messenger = 'Телефон';
$phone = '';
$spamcheck = htmlspecialchars($_POST['kuhnicheck']);

if (!empty($_SERVER['HTTP_CLIENT_IP'])) $ip = $_SERVER['HTTP_CLIENT_IP'];
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
else $ip = $_SERVER['REMOTE_ADDR'];


if (in_array($ip, [])) {
    $log = date('Y-m-d H:i:s') . " $ip";
    $log = 'evasion ' . $log;
    file_put_contents(__DIR__ . '/log.txt', $log . PHP_EOL, FILE_APPEND);
    $time30days = time() + 60 * 60 * 24 * 30;
    setcookie('evasion-protocol', '1', $time30days, '/', $_SERVER['SERVER_NAME']);
} elseif (!empty($_POST['phone'])) {
    $log = date('Y-m-d H:i:s') . ' ' . $ip;
    file_put_contents(__DIR__ . '/log.txt', $log . PHP_EOL, FILE_APPEND);
    $phone = $post['phone'];
    $subject = "Заявка с сайта";
    
    if (isset($post['name'])) {
        $content .= addRow('Имя', [$post['name']]);
        $cpParams['NAME'] = $post['name'];
    }

    if (isset($post['mail'])) $content .= addRow('Почта', [$post['mail']]);
    if (isset($post['phone'])) $content .= addRow('Телефон', [$post['phone']]);
    if (isset($post['price'])) $content .= addRow('Цена', [$post['price']]);
    $cookieHash = htmlspecialchars($_COOKIE["firstEnterPage"]);
    $cookieRef = htmlspecialchars($_COOKIE["firstReferrer"]);
    if (empty($cookieHash)) $cookieHash = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];


    ?><?php try {
        $content = strip_tags(str_replace("<br>", " \r\n", $content));
        $userId = '';
        $contactName = ''; ?><?php $amocrm = new Amocrm(
        'amocrmtest252',
        'amocrmtest252@proton.me',
        [
            'pipeline_id' => '7742798',
            'tags' => '252.суперкухни.рф',
            'status' => '63877810',
            'site' => '252.суперкухни.рф'
        ]
        ); ?><?php $amocrm->phone = filterPhone($post['phone']);
        $amocrm->message = $content;
        $amocrm->thm = $subject;
        $amocrm->price = $post['price'];

        if ($amocrm->auth()) {
            $amocrm->pushLeadAndContact();
        } else {
            echo "В AMO не отправлено, ошибка авторизации";
        }

    } catch (Exception $e) {
        echo 'Ошибка: ',  $e->getMessage(), "\n";
        exit;
    }
    ?><?php $_SESSION['spasibo'] = 1;
} ?>