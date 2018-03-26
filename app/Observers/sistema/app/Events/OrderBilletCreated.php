<?php

namespace App\Events;

use App\Entities\Order;
use App\Entities\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderBilletCreated extends Event implements ShouldQueue
{
    use SerializesModels, InteractsWithQueue;

    /**
     * @var Order
     */
    public $order;
    /**
     * @var Transaction
     */
    public $transaction;

    /**
     * Create a new event instance.
     *
     * @param Order $order
     * @param Transaction $transaction
     */
    public function __construct(Order $order, Transaction $transaction)
    {
        $this->order = $order;
        $this->transaction = $transaction;
    }
}
