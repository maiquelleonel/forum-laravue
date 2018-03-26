<?php

namespace App\Http\Requests\Admin;


class BoletoRequest extends Request
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
        return [
            'order_id'          => 'required',
            'customer_id'       => 'required',
            'cpfboleto'         => 'required',
            'due_date'          => 'required|date|after:yesterday'
        ];
    }
}