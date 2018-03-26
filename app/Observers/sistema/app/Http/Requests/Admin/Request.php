<?php

namespace App\Http\Requests\Admin;

use App\Entities\Currency;
use Illuminate\Foundation\Http\FormRequest;

class Request extends FormRequest
{
    public function rules()
    {
        return [];
    }

    public function authorize()
    {
        return true;
    }
     /**
     * Validate the input.
     *
     * @param  \Illuminate\Validation\Factory  $factory
     * @return \Illuminate\Validation\Validator
     */
    public function validator($factory)
    {
        return $factory->make(
            $this->sanitizeInput(), $this->container->call([$this, 'rules']), $this->messages()
        );
    }

    /**
     * Sanitize the input.
     *
     * @return array
     */
    protected function sanitizeInput()
    {
        if (method_exists($this, 'sanitize'))
        {
            return $this->container->call([$this, 'sanitize']);
        }

        return $this->all();
    }

    public function sanitize()
    {
        $inputs = $this->all();

        $stripInputs = [
            'total'         => 'monetary',
            'freight_value' => 'monetary',
            'value'         => 'monetary',
            'price'         => 'monetary',
            'cost'          => 'monetary',
            'retry_discount_1'=> 'monetary',
            'retry_discount_2'=> 'monetary',
            'discount'      => 'monetary',
            'due_date'      => 'date'
        ];

        foreach ($stripInputs as $key=>$type) {
            if(isset($inputs[$key])) {
                switch ($type) {
                    case 'monetary';
                        $inputs[$key] = $this->stripMonetaryValue($inputs[$key]);
                        break;

                    case 'date';
                        $inputs[$key] = $this->stripDateValue($inputs[$key]);
                        break;
                }
            }
        }

        $this->replace($inputs);

        return $inputs;
    }

    private function stripMonetaryValue($value)
    {
        if($currency = $this->detectMonetaryFormat($value)){
            if($currency->prefix){
                $value = trim(str_ireplace($currency->prefix, "", $value));
            }

            if($currency->suffix){
                $value = trim(str_ireplace($currency->suffix, "", $value));
            }

            if($currency->thousand == ","){
                $value = trim(str_ireplace($currency->thousand, "", $value));
            }

            if($currency->thousand == "." && $currency->decimal == ","){
                $value = trim(str_ireplace([".", ","], ["", "."], $value));
            }

            return (float) $value;
        }

        $value = trim(
            str_ireplace(["R$", "%", "$"], ["", "", ""], $value)
        );

        if (is_numeric($value)) {
            return (float)$value;
        }

        return (float)trim(
            str_ireplace([".", ","], ["", "."], $value)
        );
    }

    private function detectMonetaryFormat($value)
    {
        $currencies = Currency::all();

        foreach($currencies as $currency){
            if(($currency->prefix && starts_with($value, $currency->prefix))
                || ($currency->suffix && ends_with($value, $currency->suffix))){
                return $currency;
            }
        }

        return null;
    }

    private function stripDateValue($value)
    {
        if(str_contains($value, "/")){
            return implode("-", array_reverse(explode("/", $value)));
        }

        return $value;
    }
}
