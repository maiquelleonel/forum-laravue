<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $table = "payment_setting";

    protected $fillable = [
        "name",

        "billet_gateway",
        "creditcard_gateway",

        "credit_card_interest",
        "credit_card_acquirers",

        "mundipagg_merchantkey",
        "mundipagg_merchantkey_antifraud",
        "mundipagg_environment",
        "mundipagg_payment_method",
        "mundipagg_transaction_prefix",
        "mundipagg_softdescriptor",

        "asaas_apikey",
        "asaas_environment",
        "asaas_boleto_description",
        "asaas_days_expiration",

        "boleto_facil_apikey",
        "boleto_facil_environment",
        "boleto_facil_description",
        "boleto_facil_days_expiration",

        "pagseguro_email",
        "pagseguro_token",
        "pagseguro_environment",
        "pagseguro_prefix",

        "stripe_api_key",

        "paypal_environment",
        "paypal_client_id",
        "paypal_secret_key",
        "paypal_description",

        "payments",
        "retry_payments"
    ];

    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    public function callCenters()
    {
        return $this->hasMany(Site::class, "payment_setting_callcenter_id");
    }
}