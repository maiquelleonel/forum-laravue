<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property User user
 * @property Collection postBacks
 * @property Order order
 * @property string status
 * @property integer order_id
 * @property integer user_id
 * @property integer orders_qty
 * @property float orders_amount
 * @property integer commissions_paid_qty
 * @property float commissions_paid_amount
 */
class ShavingCounter extends Model
{
    protected $table = "shaving_counter";

    protected $fillable = [
        "site_id",
        "user_id",
        "config_commission_rule_origin_id",
        "config_commission_rule_payment_type_id",
        "orders_qty",
        "orders_amount",
        "commissions_paid_qty",
        "commissions_paid_amount"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function origin()
    {
        return $this->belongsTo(ConfigCommissionRuleOrigin::class, "config_commission_rule_origin_id");
    }

    public function paymentType()
    {
        return $this->belongsTo(ConfigCommissionRulePaymentType::class, "config_commission_rule_payment_type_id");
    }

    public function isFirst()
    {
        return $this->orders_qty == 0
                && $this->orders_amount == 0
                && $this->commissions_paid_qty == 0
                && $this->commissions_paid_amount == 0;
    }
}