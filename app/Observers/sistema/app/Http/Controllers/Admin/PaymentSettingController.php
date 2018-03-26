<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\LabelField;
use App\Domain\FormFields\SelectField;
use App\Domain\FormFields\TextAreaField;
use App\Domain\FormFields\TextField;
use Gateway\One\DataContract\Enum\PaymentMethodEnum;
use App\Entities\PaymentSetting;
use App\Support\SiteSettings;
use ReflectionClass;

class PaymentSettingController extends CrudController
{
    protected $duplicateAction  = true;

    /**
     * SiteController constructor.
     * @param SiteSettings $siteSettings
     * @param PaymentSetting $model
     */
    public function __construct(SiteSettings $siteSettings, PaymentSetting $model)
    {
        parent::__construct($siteSettings, $model);
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $payments = new ReflectionClass( PaymentMethodEnum::class );

        return [
            [
                new LabelField("Configurações do Pagamento"),
                new TextField("name"),
                [
                    new TextField("payments"),
                    new TextField("retry_payments"),
                    new SelectField("creditcard_gateway", config("payment.gateways.CreditCard")),
                    new SelectField("billet_gateway", config("payment.gateways.Boleto"))
                ],
                [
                    new TextField("credit_card_interest"),
                    new TextField("credit_card_acquirers"),
                ],

                new LabelField("Configurações MundiPagg"),
                [
                    new SelectField("mundipagg_environment", config("payment.environments.mundipagg")),
                    new SelectField("mundipagg_payment_method", array_flip($payments->getConstants()) ),
                ],
                [
                    new TextField("mundipagg_merchantkey"),
                    new TextField("mundipagg_merchantkey_antifraud"),
                ],
                [
                    new TextField("mundipagg_transaction_prefix"),
                    new TextField("mundipagg_softdescriptor"),
                ],

                new LabelField("Configurações Asaas"),
                [
                    new SelectField("asaas_environment", config("payment.environments.asaas")),
                    new TextField("asaas_apikey"),
                    new TextField("asaas_days_expiration")
                ],
                
                [
                    new TextAreaField("asaas_boleto_description", ['rows'=>'2']),
                ],

                new LabelField("Configurações Boleto Fácil"),
                [
                    new SelectField("boleto_facil_environment", config("payment.environments.boleto_facil")),
                    new TextField("boleto_facil_apikey"),
                    new TextField("boleto_facil_days_expiration")
                ],
                [
                    new TextAreaField("boleto_facil_description", ['rows'=>'2']),
                ],

                new LabelField("Configurações PagSeguro"),
                
                [
                    new SelectField("pagseguro_environment", config("payment.environments.pagseguro")),
                    new TextField("pagseguro_email"),
                ],
                
                [
                    new TextField("pagseguro_token"),
                    new textField("pagseguro_prefix"),
                ],

                new LabelField("Configurações do Paypal"),
                [
                    new SelectField("paypal_environment", config("payment.environments.paypal")),
                    new TextField("paypal_client_id"),
                    new TextField("paypal_secret_key"),
                    new TextField("paypal_description")
                ],

                new LabelField("Configurações do Stripe"),
                [
                    new TextField("stripe_api_key"),
                ]
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function getColumns()
    {
        return [
            "name",
            "mundipagg_environment" => function($model){
                return config("payment.environments.mundipagg.".$model->mundipagg_environment);
            },
            "asaas_environment" => function($model){
                return config("payment.environments.asaas.".$model->asaas_environment);
            },
            "pagseguro_environment" => function($model){
                return config("payment.environments.pagseguro.".$model->pagseguro_environment);
            },
            "Usado nos sites" => function ($model) {
                return $model->sites->implode("name", ", ") ?: "Nenhum site";
            },
            "Usado nos CallCenters" => function ($model) {
                return $model->callCenters->implode("name", ", ") ?: "Nenhum site";
            }
        ];
    }
}
