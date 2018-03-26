<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\HiddenField;
use App\Domain\FormFields\SelectField;
use App\Entities\ApiKey;
use App\Http\Requests\Admin\Request;
use App\Support\SiteSettings;

class ApiKeyController extends CrudController
{
    /**
     * @var $model ApiKey
     */
    protected $model;

    protected $authField = "user_id";

    protected $showId = false;

    protected $deleteAction = true;

    /**
     * ApiKeyController constructor.
     * @param SiteSettings $siteSettings
     * @param ApiKey $model
     */
    public function __construct(SiteSettings $siteSettings, ApiKey $model)
    {
        parent::__construct($siteSettings, $model);
        $this->user = auth()->user();
    }

    public function getColumns()
    {
        return [
            "user_id" => function($model){
                return $model->user->name;
            },
            "site_id" => function($model){
                return $model->site->name;
            },
            "access_token"
        ];
    }

    public function store(Request $request)
    {
        $request["user_id"]      = auth()->user()->id;
        $request["access_token"] = $this->model->generateKey();
        return parent::store($request);
    }

    public function getFields()
    {
        $sites = $this->siteSettings->getAllSites()->lists("name", "id")->toArray();

        return [
            new SelectField("site_id", $sites)
        ];
    }
}
