<?php

namespace App\Http\Requests\Admin;


class CampaignCostRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            "utm_campaign"  => "required",
            "day"           => "required",
            "month"         => "required",
            "year"          => "required",
            "cost"          => "required"
        ];

        return $rules;
    }
}
