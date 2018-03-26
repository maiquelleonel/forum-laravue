<?php

namespace App\Listeners;

use App\Domain\NotificationType;
use App\Entities\OrderNotification;
use App\Events\OrderRefund;

class SendOrderRefundMail extends OrderNotificationListener
{
    /**
     * Handle the event.
     *
     * @param OrderRefund $orderRefund
     */
    public function handle(OrderRefund $orderRefund)
    {
        $order = $orderRefund->order;

        if( !$order->notifications->where("type", NotificationType::MAIL_ORDER_REFUND)->count() ) {

            $response = $this->sendMail($order, 'admin.email.order-refund', trans("mail.order-refund-subject"));

            if ($response) {
                $order->notifications()->save( new OrderNotification([
                    "type" => NotificationType::MAIL_ORDER_REFUND
                ]));
            }
        }
    }
}
