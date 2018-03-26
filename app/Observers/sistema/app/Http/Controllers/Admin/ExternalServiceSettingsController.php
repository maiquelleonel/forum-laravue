<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\SelectField;
use App\Domain\FormFields\TextField;
use App\Domain\FormFields\CheckboxGroupField;
use App\Http\Requests;
use App\Http\Requests\Admin\Request;
use App\Entities\Site;
use App\Support\SiteSettings;
use App\Entities\ExternalServiceSettings;

class ExternalServiceSettingsController extends CrudController
{

    protected $register_per_page = 25;

    protected $deleteAction = true;
    /**
     * SiteController constructor.
     * @param SiteSettings $siteSettings
     * @param ExternalServiceSettings $model
     */
    public function __construct(SiteSettings $siteSettings, ExternalServiceSettings $model)
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
            "Evolux"        => "Evolux",
            "Slack"         => "Slack",
            //"Mautic"        => "Mautic",
            //"ActiveCampaign"=> "Active Campaign"
        ];

        $site_fields = [];
        foreach (Site::all()->sortBy('name') as $site) {
            $site_fields[$site->id] = $site->name .' ('. $site->domain .')';
        }

        return [[
            [new TextField("name")],
            [new SelectField("service", $services), new SelectField("auth_type", $authType)],
            [new TextField("username")            , new TextField("password")],
            [new TextField("oauth_secret_key")    , new TextField("oauth_client_key")],
            [new TextField("api_key")             , new TextField("base_url")],
            [new CheckboxGroupField('sites', $site_fields) ]
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

    public function store(Request $request)
    {
        $this->lastInsertedModel = $this->model->create($request->all());
        $this->lastInsertedModel->sites()->attach($request->input('sites'));
        \Cache::flush();
        return redirect()->action("\\".get_class($this). "@index");
    }

    public function update(Request $request, $id)
    {
        $model = $this->model->find($id);
        $model->update($request->all());

        $model->sites()->detach();
        $model->sites()->attach($request->input('sites'));

        $this->lastUpdatedModel = $model;
        \Cache::flush();

        return redirect()->action("\\". get_class($this) . "@index");
    }

    public function destroy($id)
    {
        $model = $this->model->find($id)
                      ->sites()->detach();
        return parent::destroy($id);
    }
}
