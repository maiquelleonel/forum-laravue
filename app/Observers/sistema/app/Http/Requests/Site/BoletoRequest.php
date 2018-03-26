<?php

namespace App\Http\Requests\Site;

use App\Http\Requests\Request;

class BoletoRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'cpfboleto' => 'required',
            'bundle_id' => 'required'
        ];
    }
}
