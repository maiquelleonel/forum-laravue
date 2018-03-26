<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class CustomerRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'firstname' => 'required|min:3',
            'lastname' => 'required|min:3',
            'email' => 'required|email',
            'telephone' => 'required|min:3',
            'postcode' => 'required|min:3',
            'address_street' => 'required|min:3',
            'address_street_number' => 'required',
            'address_street_district' => 'required',
            'address_city' => 'required|min:3',
            'address_state' => 'required'
        ];

        if ($this->isMethod(Request::METHOD_POST)) {
            $rules['email'] .= "|unique:customers,email";
            return $rules + ['site_id' => 'required'];
        }

        return $rules;
    }
}
