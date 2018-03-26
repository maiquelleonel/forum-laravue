<?php

namespace App\Repositories\Orders;

use App\Repositories\BaseEloquentRepository;
use App\Entities\Order;

/**
 * Class OrderRepositoryEloquent
 * @package namespace App\Repositories;
 */
class OrderRepository extends BaseEloquentRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Order::class;
    }

    protected $fieldSearchable = [
        'id',
        'total',
        'status',
        'payment_type',
        'invoice_id',
        'created_at'
    ];

    public function getSortableFields()
    {
        $sortableFields = [];
        foreach($this->fieldSearchable as $key) {
            $sortableFields[$key] = $key;
        }
        return $sortableFields;
    }

    public function updateTotalOrder($orderId)
    {
        $order = $this->with('itemsBundle', 'itemsProduct')
                      ->find($orderId);

        $total = 0;

        foreach($order->itemsBundle as $item) {
            $total+= $item->qty * $item->price;
        }

        foreach($order->itemsProduct as $item) {
            $total+= $item->qty * $item->price;
        }

        return $this->update([
            'total' => $total
        ], $orderId);
    }
}
