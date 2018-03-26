<?php

namespace App\Http\Requests\Site;

use App\Http\Requests\Request;

class TrackRequest extends Request
{
    public function rules()
    {
        return [
            'email'   => 'required_without_all:phone',
            'phone'   => 'required_without_all:email'
        ];
    }
}