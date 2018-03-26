<?php

namespace App\Http\Requests\Admin;

class CreditCardRequest extends Request
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
        if ( (bool)$this->get('instant_buy_key') ) {
            return [
                'installments'=>'required|between:1,12'
            ];
        }

        return [
            'order_id'  => 'required',
            'number'    => 'required',
            'cvv'       => 'required',
            'cpfcartao' => 'required',
            'name'      => 'required',
            'year'      => 'required',
            'month'     => 'required',
            'installments'=>'required|between:1,12'
        ];
    }
}
