<?php

namespace App\Http\Requests\Api\V1;

class CustomerRequest extends BaseApiRequest
{
    public function rules()
    {
        return [
            'firstname'                 => 'required',
            'lastname'                  => 'required',
            'email'                     => 'required',
            'telephone'                 => 'required',
            'postcode'                  => 'required',
            'address_street'            => 'required',
            'address_street_number'     => 'required',
            'address_street_district'   => 'required',
            'address_city'              => 'required',
            'address_state'             => 'required',

            // Tracking Variables
            "tracking.utm_source"                   => 'max:255',
            "tracking.utm_medium"                   => 'max:255',
            "tracking.utm_campaign"                 => 'max:255',
            "tracking.utm_term"                     => 'max:255',
            "tracking.utm_content"                  => 'max:255',
            "tracking.referrer"                     => 'max:500',
            "tracking.custom_var_k1"                => 'max:255',
            "tracking.custom_var_v1"                => 'max:255',
            "tracking.custom_var_k2"                => 'max:255',
            "tracking.custom_var_v2"                => 'max:255',
            "tracking.custom_var_k3"                => 'max:255',
            "tracking.custom_var_v3"                => 'max:255',
            "tracking.custom_var_k4"                => 'max:255',
            "tracking.custom_var_v4"                => 'max:255',
            "tracking.custom_var_k5"                => 'max:255',
            "tracking.custom_var_v5"                => 'max:255',
            "tracking.click_id"                     => 'max:255'
        ];
    }
}
