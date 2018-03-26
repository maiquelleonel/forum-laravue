<?php

namespace App\Services\Sms;


class Fake implements SmsDriver
{

    /**
     * Send SMS to given number
     * @param $number
     * @param $message
     * @return bool
     */
    public function send($number, $message)
    {
        return true;
    }
}