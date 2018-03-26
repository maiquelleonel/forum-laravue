<?php

namespace App\Listeners;

use App\Services\Sms\SmsDriver;
use Doctrine\DBAL\Exception\NonUniqueFieldNameException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Message;

abstract class OrderNotificationListener implements ShouldQueue
{
    /**
     * @var SmsDriver
     */
    protected $sms;

    /**
     * OrderNotificationListener constructor.
     * @param SmsDriver $sms
     */
    public function __construct(SmsDriver $sms)
    {
        $this->sms = $sms;
    }

    /**
     * Send Email to Order
     * @param $order
     * @param $view
     * @param $subject
     * @param array $customData
     * @param array $attachments
     * @return mixed
     */
    protected function sendMail($order, $view, $subject, $customData = [], $attachments = [])
    {
        if (config("mail.host") && config("mail.username") && config("mail.password")) {
            $message = function (Message $m) use ($order, $subject, $attachments) {
                $m->from($order->customer->site->company->email, $order->customer->site->company->name)
                    ->to($order->customer->email, $order->customer->firstname)
                    ->subject($subject);

                foreach ($attachments as $attachName => $contents) {
                    $m->attachData($contents, $attachName);
                }
            };

            $customer = $order->customer;

            $data = array_merge([
                "order" => $order,
                "customer" => $customer
            ], $customData);

            return \Mail::send($view, $data, $message);
        }

        return null;
    }

    /**
     * Send Sms to Order
     * @param $customer
     * @param $message
     * @return boolean
     */
    protected function sendSms($customer, $message)
    {
        return $this->sms->send($customer->telephone, $message);
    }
}
