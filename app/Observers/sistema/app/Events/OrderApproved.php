<?php

namespace App\Events;

use App\Entities\Order;
use Illuminate\Queue\SerializesModels;

class OrderApproved extends Event
{
    use SerializesModels;
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
