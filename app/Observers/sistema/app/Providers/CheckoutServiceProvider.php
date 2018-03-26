<?php

namespace App\Providers;

use App\Support\MobileDetect;
use App\Support\SiteSettings;
use Illuminate\Support\ServiceProvider;

class CheckoutServiceProvider extends ServiceProvider
{
    /**
     * @var SiteSettings
     */
    private $siteSettings;

    /**
     * Boot package
     * @param SiteSettings $siteSettings
     */
    public function boot(SiteSettings $siteSettings)
    {
        $this->siteSettings = $siteSettings;
        $this->loadLanguages();
        $this->loadViews();
        $this->bindPaymentTypes();
    }

    /**
     * Register application
     */
    public function register()
    {

    }

    /**
     * Load views and share variables
     */
    public function loadViews()
    {
        if ($site = $this->siteSettings->getSite()) {
            $theme = (Object) config("themes.".$site->theme);
            if ($theme->responsive) {
                $this->loadViewsFrom(base_path("resources/views/themes/{$theme->folder}/thankyou"), 'thankyou');
                $this->loadViewsFrom(base_path("resources/views/themes/{$theme->folder}/checkout"), 'checkout');
                $this->loadViewsFrom(base_path("resources/views/themes/{$theme->folder}/policies"), 'policy');
            } else {
                $device = !(new MobileDetect)->isMobile() ? "desktop" : "mobile";
                $this->loadViewsFrom(base_path("resources/views/themes/{$theme->folder}/{$device}/thankyou"), 'thankyou');
                $this->loadViewsFrom(base_path("resources/views/themes/{$theme->folder}/{$device}/checkout"), 'checkout');
                $this->loadViewsFrom(base_path("resources/views/themes/{$theme->folder}/{$device}/policies"), 'policy');
            }
        }
    }

    /**
     * Load Languages
     */
    public function loadLanguages()
    {
        $this->loadTranslationsFrom(base_path("resources/lang"), 'checkout');
    }

    /**
     * Bind Payment Services
     */
    private function bindPaymentTypes()
    {
        $this->bindBoletoPayment();
        $this->bindCreditCardPayment();
    }

    /**
     * Bind Boleto Payment
     */
    private function bindBoletoPayment()
    {
        // Bind Asaas Gateway
        $this->app->bind(
            config('payment.gateways.Boleto.Asaas'),
            \App\Services\Gateways\Asaas::class
        );

        // Bind Boleto Fácil Gateway
        $this->app->bind(
            config('payment.gateways.Boleto.BoletoFacil'),
            \App\Services\Gateways\BoletoFacil::class
        );
    }

    /**
     * Bind Credit Card Payment
     */
    private function bindCreditCardPayment()
    {
        // Bind MundiPagg Gateway
        $this->app->bind(
            config('payment.gateways.CreditCard.mundipagg'),
            \App\Services\Gateways\MundiPagg::class
        );

        // Bind Stripe Gateway
        $this->app->bind(
            config('payment.gateways.CreditCard.stripe'),
            \App\Services\Gateways\Stripe::class
        );
    }
}