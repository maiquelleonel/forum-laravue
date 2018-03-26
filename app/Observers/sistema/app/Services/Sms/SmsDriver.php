<?php

namespace App\Services\Sms;


interface SmsDriver
{
    /**
     * Send SMS to given number
     * @param $number
     * @param $message
     * @return boolean
     */
    public function send($number, $message);
}