<?php

namespace App\Entities;


use App\Presenters\SalesCommissionPresenter;
use Dlimars\LaravelSearchable\Searchable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;

/**
 * @property User user
 * @property Collection postBacks
 * @property Order order
 * @property string status
 * @property integer order_id
 * @property integer user_id
 */
class SalesCommission extends Model
{
    use PresentableTrait, Searchable;

    const STATUS_PENDING    = "PENDING";
    const STATUS_APPROVED   = "APPROVED";
    const STATUS_PAID       = "PAID";
    const STATUS_SHAVED     = "SHAVED";

    protected $table = "sales_commission";

    protected $fillable = [
        "order_id",
        "user_id",
        "value",
        "status",
        "paid_at",
        "sales_commission_paid_id",
        "currency_id",
        "conversion_rate"
    ];

    protected $dates = [
        "created_at",
        "updated_at",
        "paid_at"
    ];

    protected $searchable = [
        'id'            => 'MATCH',
        'order_id'      => 'MATCH',
        'user_id'       => 'MATCH',
        'status'        => 'MATCH',
        'value'         => 'BETWEEN',
        'paid_at'       => 'BETWEEN',
        'created_at'    => 'BETWEEN'
    ];

    protected $presenter = SalesCommissionPresenter::class;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function postBacks()
    {
        return $this->hasMany(PostBack::class, "user_id", "user_id");
    }

    public function commissionPaid()
    {
        return $this->belongsTo(SalesCommissionPaid::class, "sales_commission_paid_id");
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, "currency_id");
    }
}