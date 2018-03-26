<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class ErpSetting extends Model
{
    protected $table = "erp_setting";

    protected $fillable = [
        "name",
        "service",
        "discount_ipi_in_apps",
        "api_key",
        "username",
        "password",
        "billet_store_id",
        "credit_card_store_id",
        "others_store_id",
        "generate_invoice",
        "run_validations"
    ];
}
