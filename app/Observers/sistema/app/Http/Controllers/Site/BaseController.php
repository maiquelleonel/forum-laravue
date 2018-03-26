<?php

namespace App\Http\Controllers\Site;

use Illuminate\Routing\Controller;
use App\Support\SiteSettings;

class BaseController extends Controller
{
    /**
     * @var SiteSettings
     */
    protected $settings;

    /**
     * BaseController constructor.
     * @param SiteSettings $settings
     */
    public function __construct(SiteSettings $settings)
    {
        $this->settings = $settings;
        view()->share('site_settings', $settings);
        view()->share('site', $settings->getSite());
        view()->share('theme', (object) config("themes." . $settings->getSite()->theme ) );
        view()->share('view_folder', $settings->getSite()->view_folder);
        view()->share('pixel', $settings->getPixel());
        view()->share('company', $settings->getCompany());
    }
}