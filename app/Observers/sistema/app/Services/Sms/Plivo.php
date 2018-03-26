<?php

namespace App\Services\Sms;


use Plivo\RestAPI;

class Plivo implements SmsDriver
{
    private $api;

    /**
     * Plivo constructor.
     */
    public function __construct()
    {
        $this->api = new RestAPI( config('sms.username'), config('sms.api_key') );
    }

    /**
     * Send SMS to given number
     * @param $number
     * @param $message
     * @return bool
     */
    public function send($number, $message)
    {
        $response = $this->api->send_message([
            "src"   => config('sms.source'),
            "dst"   => $this->hydrateNumber( $number ),
            "text"  => $message
        ]);

        return isset($response->api_id);
    }

    private function hydrateNumber($number)
    {
        return "+55" . str_ireplace(["+", "(", ")", " ", "-"],"",$number);
    }
}