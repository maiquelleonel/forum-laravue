<?php

namespace App\Entities;

use Dlimars\LaravelSearchable\Searchable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property User user
 * @property Collection postBacks
 * @property Order order
 * @property string status
 * @property integer order_id
 * @property integer user_id
 */
class SalesCommissionPaid extends Model
{
    use Searchable;

    protected $table = "sales_commission_paid";

    protected $fillable = [
        "user_id",
        "payment_receipt"
    ];

    protected $searchable = [
        'id'            => 'MATCH',
        'user_id'       => 'MATCH',
        'created_at'    => 'BETWEEN'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function commissions()
    {
        return $this->hasMany(SalesCommission::class, "sales_commission_paid_id");
    }
}