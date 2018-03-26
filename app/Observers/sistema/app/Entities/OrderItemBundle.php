<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracasts\Presenter\PresentableTrait;
use App\Presenters\OrderItemBundlePresenter;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class OrderItemBundle extends Model implements AuditableContract
{
    use PresentableTrait, Auditable, SoftDeletes;

    protected $table = "order_item_bundle";

    protected $presenter = OrderItemBundlePresenter::class;

    protected $fillable = [
        'qty',
        'price',
        'order_id',
        'bundle_id',
        'created_at',
        'updated_at'
    ];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'bundle_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}