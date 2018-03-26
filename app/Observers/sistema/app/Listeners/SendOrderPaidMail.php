<?php

namespace App\Listeners;

use App\Domain\NotificationType;
use App\Entities\OrderNotification;
use App\Events\OrderPaid;

class SendOrderPaidMail extends OrderNotificationListener
{
    /**
     * Handle the event.
     *
     * @param OrderPaid $orderPaid
     */
    public function handle(OrderPaid $orderPaid)
    {
        $order = $orderPaid->order;

        if( !$order->notifications->where("type", NotificationType::MAIL_ORDER_PAID)->count() ) {

            $response = $this->sendMail($order, 'admin.email.order-paid', trans("mail.order-paid-subject"));

            if ($response) {
                $order->notifications()->save( new OrderNotification([
                    "type" => NotificationType::MAIL_ORDER_PAID
                ]));
            }
        }
    }
}
