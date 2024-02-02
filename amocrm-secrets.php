<?php $postData = file_get_contents('php://input');
$data = json_decode($postData, true);

define('TOKEN_FILE', $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'amocrm' . DIRECTORY_SEPARATOR . 'token_info.json');

file_put_contents(TOKEN_FILE, json_encode($data)); ?>