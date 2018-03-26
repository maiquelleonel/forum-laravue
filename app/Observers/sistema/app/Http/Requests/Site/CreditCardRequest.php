<?php

namespace App\Http\Requests\Site;

use App\Http\Requests\Request;

class CreditCardRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'number'    => 'required',
            'cvv'       => 'required',
            'cpfcartao' => 'required',
            'name'      => 'required',
            'year'      => 'required',
            'month'     => 'required',
            'installments'=>'between:1,12'
        ];
    }
}
