<?php

namespace App\Http\Controllers\Site;

use App\Repositories\Customers\CustomerRepository;
use App\Repositories\Orders\OrderRepository;
use App\Services\Pixels\AffiliatePixel;
use App\Support\SiteSettings;
use App\Domain\BundleCategory;

class AffiliatePixelController extends BaseController
{
    public function embeddedPixel($page)
    {
        /** @var $affiliatePixel \App\Services\Pixels\AffiliatePixel */
        $affiliatePixel = app(AffiliatePixel::class);
        $htmlPixels = $affiliatePixel->getCurrentHtmlPixels($this->settings->getSite(), $page);

        return view("common.embedded-affiliate-pixels", compact('page', 'htmlPixels'));
    }
}
