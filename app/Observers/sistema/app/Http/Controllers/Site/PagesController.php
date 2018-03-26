<?php

namespace App\Http\Controllers\Site;

use App\Support\MobileDetect;
use App\Support\SiteSettings;
use Illuminate\Http\Request;

class PagesController extends BaseController
{
    public function __construct(SiteSettings $settings)
    {
        parent::__construct($settings);
    }

    public function home(MobileDetect $detector)
    {
        
        session()->forget("order_id");
        session()->forget("customer_id");
        session()->forget("last_additional");

        $site = $this->settings->getSite();

        if (view()->exists($site->view_folder . ".index")) {
            return view($site->view_folder . ".index", compact("site"));
        }

        if(view()->exists($site->view_folder . ".index-mobile") && ($detector->isMobile() OR $detector->isTablet())) {
            return view($site->view_folder . ".index-mobile", compact("site"));
        }

        if(view()->exists($site->view_folder . ".index-desktop") && !$detector->isMobile()) {
            return view($site->view_folder . ".index-desktop", compact("site"));
        }

        abort(404, "View não encontrada: " . $site->view_folder);
    }
}