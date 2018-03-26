<?php

namespace App\Http\Requests\Site;

use App\Http\Requests\Request;

class CustomerRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'firstname' => 'required',
            'lastname'  => 'required',
            'email'     => 'required',
            'telephone' => 'required',
            'postcode'  => 'required',
            'address_street'            => 'required',
            'address_street_number'     => 'required',
            'address_street_district'   => 'required',
            'address_city'      => 'required',
            'address_state'     => 'required'
        ];

        if( request()->isMethod("PUT") ) {
            return $rules;
            return array_merge($rules, [
                'document_number'   => 'required'
            ]);
        }

        return array_merge($rules, [
            'site_id'   => 'required'
        ]);
    }
}
