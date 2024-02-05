<?php

use League\OAuth2\Client\Token\AccessToken;

define('TOKEN_FILE', __DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'token_info.json');

/**
 * @param array $accessToken
 */
function saveToken($accessToken)
{
    if (
        isset($accessToken)
        && isset($accessToken['accessToken'])
        && isset($accessToken['refreshToken'])
        && isset($accessToken['expires'])
        && isset($accessToken['baseDomain'])
    ) {
        $data = [
            'accessToken' => $accessToken['accessToken'],
            'expires' => $accessToken['expires'],
            'refreshToken' => $accessToken['refreshToken'],
            'baseDomain' => $accessToken['baseDomain'],
        ];
        $directory = __DIR__ . DIRECTORY_SEPARATOR . 'tmp';
        
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }        

        file_put_contents(TOKEN_FILE, json_encode($data));
        $result = file_put_contents(TOKEN_FILE, json_encode($data));

        if ($result === false) {
            echo 'Ошибка при записи файла';
        } else {
            echo 'Файл успешно записан';
        }

    } else {
        exit('Invalid access token ' . var_export($accessToken, true));
    }
}

/**
 * @return AccessToken
 */
function getToken()
{
    if (!file_exists(TOKEN_FILE)) {
        exit('Access token file not found');
    }

    $accessToken = json_decode(file_get_contents(TOKEN_FILE), true);

    if (
        isset($accessToken)
        && isset($accessToken['accessToken'])
        && isset($accessToken['refreshToken'])
        && isset($accessToken['expires'])
        && isset($accessToken['baseDomain'])
    ) {
        return new AccessToken([
            'access_token' => $accessToken['accessToken'],
            'refresh_token' => $accessToken['refreshToken'],
            'expires' => $accessToken['expires'],
            'baseDomain' => $accessToken['baseDomain'],
        ]);
    } else {
        exit('Invalid access token ' . var_export($accessToken, true));
    }
}
