<?
include_once $_SERVER['DOCUMENT_ROOT'] . 'amo/create_lead.php';


$lead_data = array();

$lead_data['NAME'] =  "Nikids";
$lead_data['PHONE'] =  "534534534";
$lead_data['EMAIL'] = "sasl@gmail.com";

$lead_data['LEAD_NAME'] = 'Заявка с сайта';

$amocrm = new AmoCRM();
$amocrm->add_lead($lead_data);
