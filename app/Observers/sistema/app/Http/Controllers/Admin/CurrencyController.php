<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\CheckboxGroupField;
use App\Domain\FormFields\HiddenField;
use App\Domain\FormFields\RadioGroupField;
use App\Domain\FormFields\TextField;
use App\Entities\ConfigCommissionRule;
use App\Entities\ConfigCommissionRuleOrigin;
use App\Entities\ConfigCommissionRulePaymentType;
use App\Entities\Currency;
use App\Entities\Site;
use App\Http\Requests;
use App\Http\Requests\Admin\Request as AdminRequest;
use App\Support\SiteSettings;
use App\Http\Requests\Admin\Request;

class CurrencyController extends CrudController
{
    /**
     * UpsellController constructor.
     * @param SiteSettings $siteSettings
     * @param Currency $model
     */
    public function __construct(SiteSettings $siteSettings, Currency $model)
    {
        parent::__construct($siteSettings, $model);
    }

    public function getColumns()
    {
        return [
            "name",
            "code",
            "prefix",
            "suffix",
            "decimals",
            "decimal",
            "thousand",
            "conversion_rate",
            "updated_at" => function($model){
                return $model->updated_at->format("d/m/Y H:i:s");
            }
        ];
    }
}
