<?php

namespace App\Http\Controllers\Admin;

use App\Support\SiteSettings;
use Arcanedev\LogViewer\Http\Controllers\LogViewerController as BaseLogController;

class LogViewerController extends BaseLogController
{
    protected $showRoute = 'admin:log-viewer.show';

    /**
     * SiteController constructor.
     * @param SiteSettings $siteSettings
     */
    public function __construct(SiteSettings $siteSettings)
    {
        parent::__construct();
        view()->share("site", $siteSettings->getSite());
        view()->share("company", $siteSettings->getCompany());
        view()->share("sites", $siteSettings->getAllSites());
    }
}
