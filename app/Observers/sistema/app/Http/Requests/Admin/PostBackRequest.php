<?php

namespace App\Http\Requests\Admin;


class PostBackRequest extends Request
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
            "site_id"   => "required",
            "url"       => "required|url|active_url"
        ];
    }
}
