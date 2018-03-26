<?php

namespace App\Listeners;

use App\Domain\NotificationType;
use App\Entities\OrderNotification;
use App\Events\OrderBilletCreated;
use App\Events\OrderShipped;

class SendOrderBilletMail extends OrderNotificationListener
{
    /**
     * Handle the event.
     *
     * @param OrderBilletCreated $orderBillet
     */
    public function handle(OrderBilletCreated $orderBillet)
    {
        $order = $orderBillet->order;
        $transact = $orderBillet->transaction;

        if ($transaction = $transact->getTransaction() ) {
            if ($transaction->getLink() && $transaction->getDueDate()) {

                $attachments = [
                    "boleto.pdf" => file_get_contents( $transaction->getLink() )
                ];

                $response = $this->sendMail(
                    $order,
                    'admin.email.billet-created',
                    trans("mail.billet-created-subject"),
                    compact("transaction"),
                    $attachments
                );

                if ($response) {
                    $order->notifications()->save( new OrderNotification([
                        "type" => NotificationType::MAIL_ORDER_BILLET_CREATED
                    ]));
                }
            }
        }
    }
}
