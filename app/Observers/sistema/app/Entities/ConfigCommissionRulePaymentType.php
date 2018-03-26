<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;

class ConfigCommissionRulePaymentType extends Model
{
    protected $table = "config_commission_rule_payment_type";

    protected $fillable = [
        "value",
        "name"
    ];
}