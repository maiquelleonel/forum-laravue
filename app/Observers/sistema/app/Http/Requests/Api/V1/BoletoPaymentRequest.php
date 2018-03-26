<?php

namespace App\Http\Requests\Api\V1;

class BoletoPaymentRequest extends BaseApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            // Cart Fields
            "cart.order_id"                         => "required_without:cart.products|numeric",
            "cart.products.*.name"                  => "required_without:cart.order_id",
            "cart.products.*.qty"                   => "required_without:cart.order_id",
            "cart.products.*.sku"                   => "required_without:cart.order_id",
            "cart.products.*.value"                 => "required_without:cart.order_id|numeric",
            "cart.discount"                         => "numeric",
            "cart.shipping"                         => "numeric",

            // Customer Fields
            'customer.customer_id'                  => 'numeric',
            'customer.firstname'                    => 'required_without_all:cart.order_id,customer.customer_id|min:3',
            'customer.lastname'                     => 'required_without_all:cart.order_id,customer.customer_id|min:3',
            'customer.email'                        => 'required_without_all:cart.order_id,customer.customer_id|email',
            'customer.telephone'                    => 'required_without_all:cart.order_id,customer.customer_id|min:3',
            'customer.postcode'                     => 'required_without_all:cart.order_id,customer.customer_id|min:3',
            'customer.address_street'               => 'required_without_all:cart.order_id,customer.customer_id|min:3',
            'customer.address_street_number'        => 'required_without_all:cart.order_id,customer.customer_id',
            'customer.address_street_district'      => 'required_without_all:cart.order_id,customer.customer_id',
            'customer.address_city'                 => 'required_without_all:cart.order_id,customer.customer_id|min:3',
            'customer.address_state'                => 'required_without_all:cart.order_id,customer.customer_id',

            // CreditCard Fields
            'payment.Boleto.due_date'               => 'required|date|date_format:Y-m-d|after:today',
            'payment.Boleto.document_number'        => 'required',

            // Tracking Variables
            "tracking.utm_source"                   => 'max:255',
            "tracking.utm_medium"                   => 'max:255',
            "tracking.utm_campaign"                 => 'max:255',
            "tracking.utm_term"                     => 'max:255',
            "tracking.utm_content"                  => 'max:255',
            "tracking.referrer"                     => 'max:500',
            "tracking.custom_var_k1"                => 'max:255',
            "tracking.custom_var_v1"                => 'max:255',
            "tracking.custom_var_k2"                => 'max:255',
            "tracking.custom_var_v2"                => 'max:255',
            "tracking.custom_var_k3"                => 'max:255',
            "tracking.custom_var_v3"                => 'max:255',
            "tracking.custom_var_k4"                => 'max:255',
            "tracking.custom_var_v4"                => 'max:255',
            "tracking.custom_var_k5"                => 'max:255',
            "tracking.custom_var_v5"                => 'max:255',
            "tracking.click_id"                     => 'max:255'
        ];
    }
}
