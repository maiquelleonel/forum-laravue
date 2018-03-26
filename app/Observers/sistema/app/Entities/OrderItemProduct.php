<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracasts\Presenter\PresentableTrait;
use App\Presenters\OrderItemProductPresenter;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class OrderItemProduct extends Model implements AuditableContract
{
    use PresentableTrait, Auditable, SoftDeletes;

    protected $table = "order_item_product";

    protected $presenter = OrderItemProductPresenter::class;

    protected $fillable = [
        'qty',
        'price',
        'order_id',
        'product_id',
        'created_at',
        'updated_at'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}