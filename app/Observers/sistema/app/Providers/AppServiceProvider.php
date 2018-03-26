<?php

namespace App\Providers;

use App\Core\Routing\UrlGenerator;
use App\Entities\ConfigCommissionRule;
use App\Entities\Customer;
use App\Entities\Order;
use App\Entities\Transaction;
use App\Observers\CommissionRuleObserver;
use App\Observers\OrderObserver;
use App\Observers\ClickIdObserver;
use App\Observers\CustomerObserver;
use App\Observers\SaveIpUserAgentObserver;
use App\Observers\RemoveFromInterestCampaignObserver;
use App\Observers\TransactionObserver;
use App\Support\MobileDetect;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bindSmsDriver();
        $this->bindCustomer();
        $this->bindDeviceDetect();
        $this->bindObservers();
        $this->loadHelpers();
        $this->loadSupport();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('url', function ($app) {
            $routes = $app['router']->getRoutes();

            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            $app->instance('routes', $routes);

            $url = new UrlGenerator(
                $routes,
                $app['request']
            );

            $url->setSessionResolver(function () {
                return $this->app['session'];
            });

            return $url;
        });
    }

    /**
     * Load Helpers
     */
    private function loadHelpers()
    {
        require app_path('Helpers/Formulas.php');
        require app_path('Helpers/Theme.php');
        require app_path('Helpers/Formatters.php');
        require app_path('Helpers/MonetaryHelper.php');
    }


    /**
     * Bind Device Detect
     */
    private function bindDeviceDetect()
    {
        $this->app->bind('device', function ($app) {
            return new MobileDetect;
        });
    }

    /**
     * Load Macros and Support files
     */
    private function loadSupport()
    {
        require app_path('Support/breadcrumbs.php');
        require app_path('Macros/form.php');
        require app_path('Macros/html.php');
    }

    /**
     * Bind Model Observers
     */
    private function bindObservers()
    {
        Order::observe(OrderObserver::class);
        Order::observe(SaveIpUserAgentObserver::class);
        Order::observe(ClickIdObserver::class);

        Customer::observe(SaveIpUserAgentObserver::class);
        Customer::observe(ClickIdObserver::class);
        Customer::observe(CustomerObserver::class);
        Customer::observe(RemoveFromInterestCampaignObserver::class);

        Transaction::observe(TransactionObserver::class);
        ConfigCommissionRule::observe(CommissionRuleObserver::class);
    }

    /**
     * Bind Customer Between sites
     */
    public function bindCustomer()
    {
        $this->app->bind('customer_id', function ($app) {
            if ($id = session('customer_id')) {
                return $id;
            }

            if ($customer = session('customer')) {
                $customer = json_decode($customer);
                if (isset($customer->id)) {
                    session(['customer_id' => $customer->id]);
                    return $customer->id;
                }
            }

            return null;
        });
    }

    private function bindSmsDriver()
    {
        if ($driver = config('sms.driver')) {
            $this->app->bind(\App\Services\Sms\SmsDriver::class, config("sms.drivers.{$driver}"));
        }
    }
}
