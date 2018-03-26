<?php

namespace App\Http\Requests\Api\V1;

class OrderRequest extends BaseApiRequest
{
    public function rules()
    {
        $rules = [
            'discount'                  => 'numeric',
            'shipping'                  => 'numeric',
        ];

        if($this->isMethod("POST")){
            $rules['customer_id']       = 'required|numeric';
            $rules["products.*.name"]   = "required";
            $rules["products.*.qty"]    = "required";
            $rules["products.*.sku"]    = "required";
            $rules["products.*.value"]  = "required";
        }

        return $rules;
    }
}
