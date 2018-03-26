<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller as BaseController;
use App\Support\SiteSettings;

abstract class Controller extends BaseController
{
    /**
     * @var SiteSettings
     */
    protected $siteSettings;

    /**
     * Controller constructor.
     * @param SiteSettings $siteSettings
     */
    public function __construct(SiteSettings $siteSettings)
    {
        $this->siteSettings = $siteSettings;
        view()->share("siteSettings", $siteSettings);
        view()->share("site", $siteSettings->getSite());
        view()->share("company", $siteSettings->getCompany());
        view()->share("sites", $siteSettings->getAllSites());
    }

    public function response($success = false, $text = "", $data = [])
    {
        return response()->json((object)[
            'success'   => $success,
            'text'      => $text,
            'data'      => $data
        ]);
    }
}
