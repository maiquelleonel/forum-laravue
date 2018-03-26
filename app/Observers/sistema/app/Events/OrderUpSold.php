<?php

namespace App\Events;

use App\Entities\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class OrderUpSold extends Event
{
    use SerializesModels;

    use Queueable;

    /**
     * @var Order
     */
    public $order;

    /**
     * Create a new event instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
