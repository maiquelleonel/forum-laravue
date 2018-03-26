<?php

namespace App\Listeners;

use App\Domain\NotificationType;
use App\Entities\OrderNotification;
use App\Events\OrderAuthorized;

class SendOrderAuthorizedMail extends OrderNotificationListener
{
    /**
     * Handle the event.
     *
     * @param OrderAuthorized $orderAuthorized
     */
    public function handle(OrderAuthorized $orderAuthorized)
    {
        $order = $orderAuthorized->order;

        if( !$order->notifications->where("type", NotificationType::MAIL_ORDER_AUTHORIZED)->count() ) {

            $response = $this->sendMail($order, 'admin.email.order-authorized', trans("mail.order-authorized-subject"));

            if ($response) {
                $order->notifications()->save( new OrderNotification([
                    "type" => NotificationType::MAIL_ORDER_AUTHORIZED
                ]));
            }
        }
    }
}
