<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Pixels extends Model
{
    protected $table = "pixels";

    protected $fillable = [
        "name",
        "page_home",
        "page_bundles",
        "page_preorder",
        "page_checkout",
        "page_checkout_retry",
        "page_upsell",
        "page_additional",
        "page_success_creditcard",
        "page_success_boleto",
        "page_success_pagseguro",
        "page_promoexit",
        "page_retargeting"
    ];

    public function sites()
    {
        return $this->hasMany(Site::class);
    }
}