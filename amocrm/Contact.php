<?php

class Contact
{

    const TOKEN_FILE = 'token_info.json';

    public static function getContact($subdomain, $phone, $headers)
    {
        #Формируем ссылку для запроса
        $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/contacts/list?query=' . $phone;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $code = (int)$code;
        $errors = array(
            301 => 'Moved permanently',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable'
        );
        try {
            #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
            if ($code != 200 && $code != 204)
                throw new \Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
        } catch (\Exception $E) {
            die('Ошибка contact: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
        }

        /**
         * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
         * нам придётся перевести ответ в формат, понятный PHP
         */
        $Response = json_decode($out, true);

        if ($out) {
            $contact_exist_id = $Response['_embedded']['contacts']['0']['id'];
        }

        return $contact_exist_id;
    }

    public static function addContact($subdomain, $lead_id, $phone, $contact_exist_id, $headers, $phone_field_id, $site)
    {
        $contacts_lead = $lead_id;
        if (!isset($contact_exist_id)) {
            //file_put_contents('test.txt','Имя контакта '.$name,FILE_APPEND | LOCK_EX);
            $contact_name = 'Заказ с ' . $site;

            if ($phone == '') {
                $phone = 'none';
            }

            $add = array(
                array(
                    'name' => $contact_name, #Имя контакта
                    'linked_leads_id' => array( #Список с айдишниками сделок контакта
                        $contacts_lead
                    ),
                    'custom_fields' => array(
                        array(
                            #Телефоны
                            'id' => $phone_field_id, #Уникальный индентификатор заполняемого дополнительного поля
                            'values' => array(
                                array(
                                    'value' => "89091443776",
                                    'enum' => 'WORK' #Мобильный
                                )
                            )
                        ),
                    )
                )
            );
            $contacts['request']['contacts']['add'] = $add;

            #Формируем ссылку для запроса
            $link = 'https://' . $subdomain . '.amocrm.ru/private/api/v2/json/contacts/set';

            $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($contacts));
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

            $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            $code = (int)$code;
            $errors = array(
                301 => 'Move*d permanently',
                400 => 'Bad request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not found',
                500 => 'Internal server error',
                502 => 'Bad gateway',
                503 => 'Service unavailable'
            );
            try {
                #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
                if ($code != 200 && $code != 204)
                    throw new \Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
            } catch (\Exception $E) {
                die('Ошибка contact2: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
            }

            /**
             * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
             * нам придётся перевести ответ в формат, понятный PHP
             */
            $Response = json_decode($out, true);
            $Response = $Response['response']['contacts']['add'];

            $output = 'ID добавленных контактов:' . PHP_EOL;
            foreach ($Response as $v)
                if (is_array($v))
                    $output .= $v['id'] . PHP_EOL;
        } else {
            $contacts['request']['contacts']['update'] = array(
                array(
                    'id' => $contact_exist_id,
                    'last_modified' => time(),
                    'linked_leads_id' => array( #Список с айдишниками сделок контакта
                        $contacts_lead
                    )
                )
            );

            $link = 'https://' . $subdomain . '.amocrm.ru/api/v4/contacts';
            $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
            #Устанавливаем необходимые опции для сеанса cURL
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
            curl_setopt($curl, CURLOPT_URL, $link);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($contacts));
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

            $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $code = (int)$code;
            $errors = array(
                301 => 'Moved permanently',
                400 => 'Bad request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not found',
                500 => 'Internal server error',
                502 => 'Bad gateway',
                503 => 'Service unavailable'
            );
            try {
                #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
                if ($code != 200 && $code != 204)
                    throw new \Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
            } catch (\Exception $E) {
                die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
            }
        }
    }
}
