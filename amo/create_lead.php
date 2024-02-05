<?php

use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\LeadModel;
use League\OAuth2\Client\Token\AccessTokenInterface;
use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;

class AmoCRM
{
  public function add_lead($lead_data)
  {
    include_once __DIR__ . '/bootstrap.php';

    // $name = $lead_data['NAME'];
    // $phone = $lead_data['PHONE'];
    // $email = $lead_data['EMAIL'];
    // $companyName = $lead_data['COMPANY'];
    // $description = $lead_data['TEXT'];
    // $city = $lead_data['CITY'];
    $leadName = 'Заявка с сайта';

    $name = 'Niksda';
    $phone = '8982932323';
    $email = 'zy@rs.ru';

    $accessToken = getToken();

    $apiClient->setAccessToken($accessToken)
      ->setAccountBaseDomain($accessToken->getValues()['baseDomain'])
      ->onAccessTokenRefresh(
        function (AccessTokenInterface $accessToken, string $baseDomain) {
          saveToken([
            'accessToken' => $accessToken->getToken(),
            'refreshToken' => $accessToken->getRefreshToken(),
            'expires' => $accessToken->getExpires(),
            'baseDomain' => $baseDomain,
          ]);
        }
      );


    //Представим, что у нас есть данные, полученные из сторонней системы
    $externalData = [
      [
        'price' => 54321,
        'name' => 'Lead name',
        'status_id' => 63877810,
        'pipeline_id' => 7742798,
        'contact' => [
          'first_name' => 'Ivan',
          'last_name' => 'Zinoviev',
          'phone' => '+79129876543',
          'mail' => 'sss@gmail.com',
        ]
      ]
    ];

    $leadsCollection = new LeadsCollection();
    $userNumericValue = 1;


    //Создадим модели и заполним ими коллекцию
    foreach ($externalData as $externalLead) {
      //Создадим коллекцию полей сущности
      $leadCustomFieldsValues = new CustomFieldsValuesCollection();
      //Создадим модель значений поля типа текст
      $textCustomFieldValuesModel = new TextCustomFieldValuesModel();
      //Укажем ID поля
      $textCustomFieldValuesModel->setFieldId(1066241);
      //Добавим значения
      $textCustomFieldValuesModel->setValues(
        (new TextCustomFieldValueCollection())
          ->add((new TextCustomFieldValueModel())->setValue('Более 30 сек'))
      );
      //Добавим значение в коллекцию полей сущности
      $leadCustomFieldsValues->add($textCustomFieldValuesModel);

      $lead = (new LeadModel())
        ->setName($externalLead['name'])
        ->setPrice($externalLead['price'])
        ->setStatusId($externalLead['status_id'])
        ->setPipelineId($externalLead['pipeline_id'])
        ->setCustomFieldsValues($leadCustomFieldsValues)
        ->setContacts(
          (new ContactsCollection())
            ->add(
              (new ContactModel())
                ->setFirstName($externalLead['contact']['first_name'])
                ->setLastName($externalLead['contact']['last_name'])
                ->setCustomFieldsValues(
                  (new CustomFieldsValuesCollection())
                    ->add(
                      (new MultitextCustomFieldValuesModel())
                        ->setFieldCode('PHONE')
                        ->setValues(
                          (new MultitextCustomFieldValueCollection())
                            ->add(
                              (new MultitextCustomFieldValueModel())
                                ->setValue($externalLead['contact']['phone'])
                            )
                        )
                    )
                    ->add(
                      (new MultitextCustomFieldValuesModel())
                        ->setFieldCode('EMAIL')  // Код кастомного поля для email
                        ->setValues(
                          (new MultitextCustomFieldValueCollection())
                            ->add(
                              (new MultitextCustomFieldValueModel())
                                ->setValue($externalLead['contact']['mail'])
                            )
                        )
                    )
                )
            )
        );



      $leadsCollection->add($lead);
    }

    //Создадим сделки
    try {
      $apiClient->leads()->addComplex($leadsCollection);
    } catch (AmoCRMApiException $e) {
      printError($e);
      die;
    }
  }
}
