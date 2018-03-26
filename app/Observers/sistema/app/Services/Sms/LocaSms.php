<?php

namespace App\Services\Sms;


use GuzzleHttp\Client;

class LocaSms implements SmsDriver
{

    /**
     * Send SMS to given number
     * @param $number
     * @param $message
     * @return boolean
     */
    public function send($number, $message)
    {
        //$client = new Client(["base_uri"=>"http://54.173.24.177/"]);
        $client = new Client([ "base_uri" => "http://app.locasms.com.br"]);
        $response = $client->request("GET", "painel/api.ashx", [
            "query" => [
                "action"    => "sendsms",
                "lgn"       => config("sms.username"),
                "pwd"       => config("sms.password"),
                "msg"       => $message,
                "numbers"   => $this->hydrateNumber($number)
            ]
        ]);

        $response = json_decode($response->getBody());

        return isset($response->msg) && $response->msg == "SUCESSO";
    }

    private function hydrateNumber($number)
    {
        return str_ireplace(["+", "(", ")", " ", "-"],"",$number);
    }
}
