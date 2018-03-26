<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\SelectField;
use App\Domain\FormFields\TextField;
use App\Entities\EmailCampaignSetting;
use App\Http\Requests;
use App\Http\Requests\Admin\Request;
use App\Entities\Company;
use App\Entities\PaymentSetting;
use App\Entities\Pixels;
use App\Entities\Site;
use App\Support\SiteSettings;

class EmailCampaignSettingController extends CrudController
{
    /**
     * SiteController constructor.
     * @param SiteSettings $siteSettings
     * @param EmailCampaignSetting $model
     */
    public function __construct(SiteSettings $siteSettings, EmailCampaignSetting $model)
    {
        parent::__construct($siteSettings, $model);
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $authType = [
            "BasicAuth" => "BasicAuth",
            "OAuth"     => "OAuth",
            "OAuth2"    => "OAuth2",
            "DataBase"  => "DataBase"
        ];

        $services = [
            "Mautic"        => "Mautic",
            'Evolux'        => "Evolux",
            //"ActiveCampaign"=> "Active Campaign"
        ];

        return [[
            [new TextField("name")],
            [new SelectField("service", $services), new SelectField("auth_type", $authType)],
            [new TextField("username")            , new TextField("password")],
            [new TextField("oauth_secret_key")    , new TextField("oauth_client_key")],
            [new TextField("api_key")             , new TextField("base_url")]
        ]];
    }

    public function getColumns()
    {
        return [
            "name",
            "service",
            "auth_type",
            "base_url"
        ];
    }

}
