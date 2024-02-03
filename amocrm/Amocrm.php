<?php

class Amocrm
{
    public $subdomain;
    public $thm;
    public $phone;
    public $message;
    public $status;    
    public $price;
    private $login;
    private $phone_field_id;
    private $pipeline_id;
    private $tags;
    private $headers;
    private $site;

    const TOKEN_FILE = 'token_info.json';

    public function __construct($subdomain, $login, $options = [])
    {
        $this->subdomain = $subdomain;
        $this->login = $login;
        if (count($options) > 0) {
            foreach ($options as $key => $value) {
                $this->$key = $value;
            }
        }

    }

    public function auth()
    {
        $auth_status = Auth::authStatus($this->subdomain);
        $json = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'amocrm' . DIRECTORY_SEPARATOR . 'token_info.json'), true);
        $access_token = $json['accessToken'];
        $this->headers = [
            'Authorization: Bearer ' . $access_token
        ];
        return $auth_status;
    }

    public function pushLeadAndContact()
    {
        $tmp_status = $this->status;
        $pipeline = $this->pipeline_id;
        $tags = $this->tags;
        $phone_field_id = $this->phone_field_id;
        $site = $this->site;
        $lead = Lead::addLead($this->subdomain, $tmp_status, $this->thm, $pipeline, $tags, $this->headers, $this->phone, $this->price);
        $getContact = Contact::getContact($this->subdomain, $this->phone, $this->headers);
        Contact::addContact($this->subdomain, $lead, $this->phone, $getContact, $this->headers, $phone_field_id, $site);
        Lead::addNote($this->subdomain, $this->message, $lead, $this->headers);
    }

}