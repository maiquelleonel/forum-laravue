<?php

namespace App\Http\Requests\Admin;

class AdditionalRequest extends Request
{
    public function rules()
    {
        return [
            "name"              => "required",
            "from_bundle_id"    => "required",
            "product_id"        => "required",
            "qty_max"           => "required|numeric",
            "price"             => "required"
        ];
    }
}