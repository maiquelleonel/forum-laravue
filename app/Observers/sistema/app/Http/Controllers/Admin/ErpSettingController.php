<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\BooleanField;
use App\Domain\FormFields\LabelField;
use App\Domain\FormFields\SelectField;
use App\Domain\FormFields\TextField;
use App\Entities\EmailCampaignSetting;
use App\Entities\ErpSetting;
use App\Http\Requests;
use App\Http\Requests\Admin\Request;
use App\Entities\Company;
use App\Entities\PaymentSetting;
use App\Entities\Pixels;
use App\Entities\Site;
use App\Support\SiteSettings;

class ErpSettingController extends CrudController
{
    /**
     * SiteController constructor.
     * @param SiteSettings $siteSettings
     * @param ErpSetting $model
     */
    public function __construct(SiteSettings $siteSettings, ErpSetting $model)
    {
        parent::__construct($siteSettings, $model);
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $services = [
            "Bling"     => "Bling"
        ];

        return [
            [
                new LabelField("Configurações gerais"),
                [new TextField("name")],
                [
                    new SelectField("service", $services),
                    new BooleanField("discount_ipi_in_apps"),
                    new BooleanField("generate_invoice"),
                    new BooleanField("run_validations")
                ],
                [new TextField("api_key"), new TextField("username"), new TextField("password")],

                new LabelField("Lojas Por Forma de Pagamento (Opcional)"),
                [
                    new TextField('billet_store_id'),
                    new TextField('credit_card_store_id'),
                    new TextField('others_store_id')
                ]
            ]
        ];
    }

    public function getColumns()
    {
        return [
            "name",
            "service",
            "api_key",
            "run_validations" => function($model){
                return $model->run_validations ? "Sim" : "Não";
            },
            "generate_invoice" => function($model){
                return $model->generate_invoice ? "Sim" : "Não";
            },
        ];
    }

}
