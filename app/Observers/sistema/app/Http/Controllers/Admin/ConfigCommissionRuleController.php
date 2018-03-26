<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\CheckboxGroupField;
use App\Domain\FormFields\HiddenField;
use App\Domain\FormFields\RadioGroupField;
use App\Domain\FormFields\SelectField;
use App\Domain\FormFields\TextField;
use App\Entities\ConfigCommissionRule;
use App\Entities\ConfigCommissionRuleOrigin;
use App\Entities\ConfigCommissionRulePaymentType;
use App\Entities\Site;
use App\Http\Requests;
use App\Http\Requests\Admin\Request as AdminRequest;
use App\Repositories\Currency\CurrencyRepository;
use App\Support\SiteSettings;
use App\Http\Requests\Admin\Request;

class ConfigCommissionRuleController extends CrudController
{
    protected $deleteAction = true;

    /**
     * UpsellController constructor.
     * @param SiteSettings $siteSettings
     * @param ConfigCommissionRule $model
     */
    public function __construct(SiteSettings $siteSettings, ConfigCommissionRule $model)
    {
        parent::__construct($siteSettings, $model);
        $this->backAction = action(ConfigCommissionGroupController::class . "@edit", request("config_commission_group_id"));
    }

    public function store(Request $request)
    {
        $request = app(AdminRequest::class);
        $response = parent::store($request);

        $this->lastInsertedModel->sites()->attach( $request->input('sites', []));
        $this->lastInsertedModel->origins()->attach( $request->input('origins', []));
        $this->lastInsertedModel->paymentTypes()->attach( $request->input('paymentTypes', []));

        return $response;
    }

    public function update(Request $request, $id)
    {
        $request = app(AdminRequest::class);
        $response = parent::update($request, $id);

        $this->lastUpdatedModel->sites()->sync( $request->input('sites', []));
        $this->lastUpdatedModel->origins()->sync( $request->input('origins', []));
        $this->lastUpdatedModel->paymentTypes()->sync( $request->input('paymentTypes', []));

        return $response;
    }

    public function getFields()
    {
        $origins    = ConfigCommissionRuleOrigin::lists("name", "id");
        $payments   = ConfigCommissionRulePaymentType::lists("name", "id");
        $sites      = Site::lists("name", "id");
        $types      = ["PERCENTAGE" => "Percentual", "FIXED" => "Valor Fixo"];
        $currencies = app(CurrencyRepository::class)->toSelectArray();
        $this->dataShared['monetary_formatter'] = app(CurrencyRepository::class)->all();

        return [
            [
                new HiddenField("config_commission_group_id"),
                [
                    new SelectField("currency_id", $currencies),
                    new TextField("value", ["label"=>"Comissão"]),
                    new TextField("shaving_rate", ["label"=>"Shaving"])
                ],
                [
                    new CheckboxGroupField("origins", $origins),
                    new CheckboxGroupField("paymentTypes", $payments),
                    new RadioGroupField("type", $types)
                ],
                new CheckboxGroupField("sites", $sites, ["data-split"=>3]),
            ]
        ];
    }
}
