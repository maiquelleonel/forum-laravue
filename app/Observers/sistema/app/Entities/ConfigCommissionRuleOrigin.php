<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;

class ConfigCommissionRuleOrigin extends Model
{
    protected $table = "config_commission_rule_origin";

    protected $fillable = [
        "value",
        "name"
    ];
}