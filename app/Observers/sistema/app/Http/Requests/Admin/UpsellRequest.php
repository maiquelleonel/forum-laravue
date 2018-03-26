<?php

namespace App\Http\Requests\Admin;

class UpsellRequest extends Request
{
    public function rules()
    {
        return [
            "from_bundle_id"    => "required",
            "to_bundle_id"      => "required"
        ];
    }
}