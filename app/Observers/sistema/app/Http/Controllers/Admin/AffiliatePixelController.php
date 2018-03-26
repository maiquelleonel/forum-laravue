<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\SelectField;
use App\Domain\FormFields\TextAreaField;
use App\Domain\FormFields\TextField;
use App\Entities\AffiliatePixel;
use App\Entities\Site;
use App\Http\Requests\Admin\Request;
use App\Support\SiteSettings;

class AffiliatePixelController extends CrudController
{
    protected $deleteAction = true;

    protected $showId = false;

    /**
     * SiteController constructor.
     * @param SiteSettings $siteSettings
     * @param AffiliatePixel $model
     */
    public function __construct(SiteSettings $siteSettings, AffiliatePixel $model)
    {
        parent::__construct($siteSettings, $model);
        $this->user = auth()->user();
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $siteIds = $this->model->where('user_id', $this->user->id)->lists("site_id");
        $sites = Site::whereIn("id", $siteIds->toArray())->lists("name", "id");
        $sites = $sites->union($this->user->offerSites->lists("name", "id"));

        $this->dataShared['sites'] = collect([""=>trans("validation.attributes.select")])->union($sites);

        $this->dataShared['allowedVars'] = array_merge(
            config("tracking.utm_vars"),
            config("tracking.custom_vars"),
            config("tracking.extra_vars")
        );

        $sites = $this->user->offerSites->lists("name", "id");
        $pages = trans("pages");

        return [
            [
                [
                    new TextField("name"),
                    new SelectField("site_id", $sites, ['label'=>'offer']),
                    new SelectField("page", $pages),
                ],
                new TextAreaField("code")
            ]
        ];
    }

    public function getColumns()
    {
        return [
            "name",
            "page" => function($pixel){
                return trans("pages.{$pixel->page}");
            },
            "offer" => function ($pixel) {
                return $pixel->site->name;
            },
        ];
    }

    public function store(Request $request)
    {
        $request->merge([
            "user_id"   => $this->user->id
        ]);

        return parent::store($request);
    }
}
