<?php

namespace App\Listeners;

use App\Domain\NotificationType;
use App\Entities\OrderNotification;
use App\Events\OrderPaid;

class SendOrderPaidSms extends OrderNotificationListener
{
    /**
     * Handle the event.
     *
     * @param OrderPaid $orderPaid
     */
    public function handle(OrderPaid $orderPaid)
    {
        $order = $orderPaid->order;

        if( !$order->notifications->where("type", NotificationType::SMS_ORDER_PAID)->count() ) {

            $response = $this->sendSms($order->customer, trans("sms.order-paid-message"));

            if ($response) {
                $order->notifications()->save( new OrderNotification([
                    "type" => NotificationType::SMS_ORDER_PAID
                ]));
            }
        }
    }
}
