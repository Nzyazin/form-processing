<?php

class Auth
{

    const AUTH_OK = '1';
    const AUTH_NONE = '0';

    public static function authStatus($subdomain)
    {
        #Формируем ссылку для запроса
        $link = 'https://' . $subdomain . '.amocrm.ru/oauth2/access_token';
        define('TOKEN_FILE', $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'amocrm' . DIRECTORY_SEPARATOR . 'token_info.json');
        $json = json_decode(file_get_contents(TOKEN_FILE), true);


        if ($json['expires'] < time()){
            $data = [
                'client_id' => $json['client_id'],
                'client_secret' => $json['client_secret'],
                'grant_type' => 'refresh_token',
                'refresh_token' => $json['refreshToken'],
                'redirect_uri' => $json['redirect_uri'],
            ];


            $curl = curl_init(); //Сохраняем дескриптор сеанса cURL
            /** Устанавливаем необходимые опции для сеанса cURL  */
            curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
            curl_setopt($curl,CURLOPT_URL, $link);
            curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
            curl_setopt($curl,CURLOPT_HEADER, false);
            curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
            $out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            /** Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
            $code = (int)$code;
            $errors = [
                400 => 'Bad request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not found',
                500 => 'Internal server error',
                502 => 'Bad gateway',
                503 => 'Service unavailable',
            ];

            try
            {
                /** Если код ответа не успешный - возвращаем сообщение об ошибке  */
                if ($code < 200 || $code > 204) {
                    throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
                }
            }
            catch(\Exception $e)
            {
                die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
            }

            /**
             * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
             * нам придётся перевести ответ в формат, понятный PHP
             */
            $response = json_decode($out, true);

            $auth_data = isset($response['access_token']) && !is_null($response['access_token']) ? self::AUTH_OK : self::AUTH_NONE;

            $data = [
                'accessToken' => $response['access_token'],
                'expires' => $response['expires_in'] + time(),
                'refreshToken' => $response['refresh_token'],
                'token_type' => $response['token_type'],
                'client_secret' => $json['client_secret'],
                'redirect_uri' => $json['redirect_uri'],
                'client_id' =>  $json['client_id'],
            ];

            file_put_contents(TOKEN_FILE, json_encode($data));
        } else {
            $auth_data = self::AUTH_OK;
        }

        return $auth_data;
    }


}