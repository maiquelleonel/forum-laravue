<?php

namespace App\Http\Controllers\Admin;

use App\Entities\PostBack;
use App\Entities\Site;
use App\Entities\User;
use App\Http\Requests\Admin\PostBackRequest;
use App\Http\Requests;
use App\Support\SiteSettings;
use App\Http\Requests\Admin\Request;

class PostBackController extends CrudController
{
    /**
     * @var bool
     */
    protected $showId = false;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var bool
     */
    protected $deleteAction = true;

    /**
     * SiteController constructor.
     * @param SiteSettings $siteSettings
     * @param PostBack $model
     */
    public function __construct(SiteSettings $siteSettings, PostBack $model)
    {
        parent::__construct($siteSettings, $model);
        $this->user = auth()->user();
    }

    public function getFields()
    {
        $siteIds = $this->model->where('user_id', $this->user->id)->lists("site_id");
        $sites = Site::whereIn("id", $siteIds->toArray())->lists("name", "id");
        $sites = $sites->union($this->user->offerSites->lists("name", "id"));

        $this->dataShared['sites'] = collect([""=>trans("validation.attributes.select")])->union($sites);

        $this->dataShared['methods'] = [
            "GET"   => "GET",
            "POST"  => "POST",
        ];

        $this->dataShared['allowedVars'] = array_merge(
            config("tracking.utm_vars"),
            config("tracking.custom_vars"),
            config("tracking.extra_vars")
        );

        return [];
    }

    public function getColumns()
    {
        return [
            trans("validation.attributes.offer") => function($model){
                return $model->site->name;
            },
            trans("validation.attributes.url")   => function($model){
                return $model->method . " | " . $model->url;
            }
        ];
    }

    public function store(Request $request)
    {
        $request = app(PostBackRequest::class);

        $request->merge([
            "user_id"   => $this->user->id
        ]);

        return parent::store($request);
    }

    public function update(Request $request, $id)
    {
        $request = app(PostBackRequest::class);
        return parent::update($request, $id);
    }
}
