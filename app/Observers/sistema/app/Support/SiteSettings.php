<?php

namespace App\Support;

use App\Exceptions\SiteNotFoundException;
use Illuminate\Http\Request;
use App\Entities\Company;
use App\Entities\PaymentSetting;
use App\Entities\Pixels;
use App\Entities\Site;

class SiteSettings
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var PaymentSetting
     */
    private $payment;

    /**
     * @var PaymentSetting
     */
    private $callCenterPayment;

    /**
     * @var Site
     */
    private $site;

    /**
     * @var Pixels
     */
    private $pixel;

    /**
     * @var Company
     */
    private $company;

    private $auto_refund;

    private $domain_must_redirect_to_rt;

    /**
     * SiteSettings constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->init();
    }

    public function init(Site $site = null)
    {
        $site = $site ?: $this->getSiteModel();

        if ( $site ) {
            $this->payment                    = $site->paymentSetting;
            $this->callCenterPayment          = $site->callCenterPaymentSetting ?: $site->paymentSetting;
            $this->site                       = $site;
            $this->pixel                      = $site->pixel ?: new Pixels;
            $this->company                    = $site->company ?: new Company;
            $this->auto_refund                = $site->auto_refund;
            $this->domain_must_redirect_to_rt = $site->domain_must_redirect_to_rt;
            return;
        }

        if( php_sapi_name() == "cli" ) {
            return;
        }

        throw new SiteNotFoundException("Site Não Encontrado! "
            . $this->request->url()
            . " IP " . ($this->request->server("HTTP_CF_CONNECTING_IP") ?: $this->request->ip()) );
    }

    private function getSiteModel()
    {
        try {
            $domain = $this->getCurrentDomain();
            $version = $this->request->route("version");

            return \Cache::remember($domain.$version, 30, function() use ($domain, $version){
                return $this->findSite($domain, $version);
            });
        } catch (\Exception $e) {}

        return null;
    }

    private function findSite($domain, $version) {
        $query = Site::query()
                        ->where(function($query) use ($domain) {
                            $query->where("domain", $domain)->orWhere("remarketing_domain", $domain);
                        })
                        ->with("paymentSetting", "pixel", "company", "bundles");

        if ($version && $version != 'v1' && $version != '1') {
            $versioned = clone $query;
            if ($site = $versioned->where("path_version", $version)->first()) {
                return $site;
            }
        }

        if($site =  $query->first()){
            return $site;
        }

        return $this->findByWildCard($domain);
    }

    private function findByWildCard($domain)
    {
        $sites = Site::query()
                        ->where("domain", "LIKE", "%*%")
                        ->orWhere("remarketing_domain", "LIKE", "%*%")
                        ->with("paymentSetting", "pixel", "company", "bundles")
                        ->get();

        foreach ($sites as $site) {
            if(str_is($site->domain, $domain) || str_is($site->remarketing_domain, $domain)){
                return $site;
            }
        }

        return null;
    }

    public function getSite()
    {
        return $this->site;
    }

    public function getAllSites()
    {
        return \Cache::remember("sites", 30, function () {
            return Site::with("paymentSetting", "pixel", "company", "bundles")->orderBy('name')->get();
        });
    }

    public function getPaymentSettings()
    {
        return $this->payment;
    }

    public function getCallCenterPaymentSettings()
    {
        return $this->callCenterPayment;
    }

    public function getPixel()
    {
        return $this->pixel;
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function isRemarketing() {
        $site = $this->getSite();

        return ($site && $site->remarketing_domain)
                ? str_is($site->remarketing_domain, $this->getCurrentDomain())
                : false;
    }

    private function getCurrentDomain()
    {
        return explode(":", $this->request->server("HTTP_HOST"))[0];
    }
}
