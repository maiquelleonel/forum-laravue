<?php
/**
 * @var Router $router
 */

// Admin Routes
use Illuminate\Routing\Router;

$router->group(['as' => 'admin:', 'middleware'=>'change_user_locale'], function(Router $router){

    /**
     * Auth Routes
     */
    $router->get('auth/login', ['as' => 'auth.index', 'uses' => 'Auth\AuthController@getLogin']);
    $router->post('auth/login', ['as' => 'auth.login', 'uses' => 'Auth\AuthController@postLogin']);
    $router->get('auth/logout', ['as' => 'auth.logout', 'uses' => 'Auth\AuthController@getLogout']);

    $router->group(['middleware' => ['auth']], function(Router $router) {

        /**
         * Protected Routes
         */
        $router->group(['middleware'=>['routeNeedsPermission']], function(Router $router){

            /**
             * Dashboard Routes
             */
            $router->get('/', ['as' => 'dashboard', 'uses' => 'DashboardController@index']);

            /**
             * User Routes
             */
            $router->get('users', ['as'=>'users.index', 'uses'=>'UserController@index']);
            $router->get('deleted-users', ['as'=>'deleted-users.index', 'uses'=>'DeletedUserController@index']);
            $router->get('users/create', ['as'=>'users.create', 'uses'=>'UserController@create']);
            $router->get('users/{user}/edit', ['as'=>'users.edit', 'uses'=>'UserController@edit']);
            $router->post('users', ['as'=>'users.store', 'uses'=>'UserController@store']);
            $router->put('users/{user}', ['as'=>'users.update', 'uses'=>'UserController@update']);

            /**
             * Customer Routes
             */
            $router->get('/customers', ['as' => 'customers.index', 'uses' => 'CustomerController@index']);
            $router->get('/delayed-customers', ['as' => 'delayed-customers.index', 'uses' => 'DelayedCustomerController@index']);
            $router->get('/customer/create', ['as' => 'customers.create', 'uses' => 'CustomerController@create']);
            $router->post('/customer', ['as' => 'customers.store', 'uses' => 'CustomerController@store']);
            $router->get('/customer/canceled', ['as' => 'customers.canceled', 'uses' => 'CustomerController@canceled']);
            $router->get('/delayed-customer/canceled', ['as' => 'delayed-customers.canceled', 'uses' => 'DelayedCustomerController@canceled']);
            $router->get('/customer/interested', ['as' => 'customers.interested', 'uses' => 'CustomerController@interested']);
            $router->get('/delayed-customer/interested', ['as' => 'delayed-customers.interested', 'uses' => 'DelayedCustomerController@interested']);
            $router->get('/customer/{customer}', ['as' => 'customers.show', 'uses' => 'CustomerController@show']);
            $router->put('/customer/{customer}', ['as' => 'customers.update', 'uses' => 'CustomerController@update']);
            $router->get('/customer/{customer}/edit', ['as' => 'customers.edit', 'uses' => 'CustomerController@edit']);

            /**
             * Order Routes
             */
            $router->get('/orders', ['as' => 'orders.index', 'uses' => 'OrderController@index']);
            $router->get('/orders/without-invoice', ['as' => 'orders.without-invoice', 'uses' => 'OrderController@withoutInvoice']);
            $router->post('/order', ['as' => 'orders.store', 'uses' => 'OrderController@store']);
            $router->put('/order/{order}', ['as' => 'orders.update', 'uses' => 'OrderController@update']);
            $router->put('/order/{orderId}/status', ['as' => 'orders.update-status', 'uses' => 'OrderController@update']);
            $router->put('/order/{orderId}/invoice-number', ['as' => 'orders.update-invoice-number', 'uses' => 'OrderController@update']);
            $router->put('/order/{order}/vendor', ['as' => 'orders.update-vendor', 'uses' => 'OrderController@update']);
            $router->get('/order/{order}', ['as' => 'orders.show', 'uses' => 'OrderController@show']);
            $router->post('/order/integrate/{orderId}', ['as' => 'orders.integrate', 'uses' => 'OrderController@integrate']);
            $router->post('/order/integrate-now/{orderId}', ['as' => 'orders.integrate-now', 'uses' => 'OrderController@integrateNow']);
            $router->post('/order/store-upsell/{orderId}', ['as' => 'orders.store-upsell', 'uses' => 'OrderController@storeUpsell']);

            /**
             * OrderItem Routes
             */
            $router->post('/order-items-bundle/{orderId}', ['as' => 'orderitembundle.store', 'uses' => 'OrderItemController@storeBundle']);
            $router->post('/order-items-product/{orderId}', ['as' => 'orderitemproduct.store', 'uses' => 'OrderItemController@storeProduct']);
            $router->delete('/order-items-bundle/{orderId}', ['as' => 'orderitembundle.destroy', 'uses' => 'OrderItemController@destroyBundle']);
            $router->delete('/order-items-product/{orderId}', ['as' => 'orderitemproduct.destroy', 'uses' => 'OrderItemController@destroyProduct']);

            /**
             * Checkout Routes
             */
            $router->post('checkout/credit-card', ['as' => 'checkout.credit-card', 'uses'=>'CheckoutController@paymentCreditCard']);
            $router->post('checkout/boleto', ['as' => 'checkout.boleto', 'uses'=>'CheckoutController@paymentBoleto']);
            $router->post('checkout/pagseguro', ['as' => 'checkout.pagseguro', 'uses'=>'CheckoutController@paymentPagSeguro']);

            /**
             * Report Routes
             */
            $router->get('/report/charts', ['as'=>'report.charts', 'uses' => 'ReportController@charts']);
            $router->get('/report/vendors', ['as'=>'report.vendors', 'uses' => 'ReportController@vendors']);
            $router->get('/report/bundles', ['as'=>'report.bundles', 'uses' => 'ReportController@bundles']);
            $router->get('/report/comparative-tables', ['as'=>'report.comparative-tables',
            'uses' => 'ReportController@comparativeTables']);
            $router->get('/report/seller/{userId}', ['as'=>'report.extract-seller',
            'uses' => 'ReportController@extractSeller']);
            $router->get('/report/campaigns-orders', ['as'=>'report.campaigns-orders',
            'uses' => 'ReportController@campaignsOrders']);
            $router->get('/report/campaigns-orders/details', ['as'=>'report.campaigns-orders.details',
            'uses' => 'ReportController@detailedCampaignsOrders']);
            $router->get('/report/campaigns-leads', ['as'=>'report.campaigns-leads',
            'uses' => 'ReportController@campaignsLeads']);
            $router->get('/report/evolux-login', ['as' => 'report.evolux-login',
            'uses' => 'ReportController@evoluxLogin']);
            $router->get('/report/evolux-login-process', ['as' => "report.evolux-login-process",
            "uses" =>"ReportController@evoluxLoginProcess"]);
            $router->get('/report/products-sold', ['as' => "report.products-sold",
            "uses" =>"ReportController@productsSold"]);
            $router->get('/report/bundles-sold', ['as' => "report.bundles-sold",
            "uses" =>"ReportController@bundlesSold"]);

            /**
             * Billing Costs
             */
            $router->get('/billing/campaign-costs', ['as'=>'billing.campaign-costs.index', 'uses' => 'CampaignCostsController@index']);
            $router->post('/billing/campaign-costs', ['as'=>'billing.campaign-costs.store', 'uses' => 'CampaignCostsController@store']);

            /**
             * Transaction Routes
             */
            $router->any('/transaction/refund/{transactionId}', ['as' => 'transaction.refund' , 'uses' => 'TransactionController@refund']);
            $router->any('/transaction/capture/{transactionId}', ['as' => 'transaction.capture' , 'uses' => 'TransactionController@capture']);

            /**
             * Products Controller
             */
            $router->get('/products', ['as' => 'product.index', 'uses' => 'ProductController@index']);
            $router->get('/products/create', ['as' => 'product.create', 'uses' => 'ProductController@create']);
            $router->get('/products/{productId}/edit', ['as' => 'product.edit', 'uses' => 'ProductController@edit']);
            $router->put('/products/{productId}', ['as' => 'product.update', 'uses' => 'ProductController@update']);
            $router->post('/products', ['as' => 'product.store', 'uses' => 'ProductController@store']);

            /**
             * Bundle Controller
             */
            $router->get('/bundle', ['as' => 'bundle.index', 'uses' => 'BundleController@index']);
            $router->get('/bundles/create', ['as' => 'bundle.create', 'uses' => 'BundleController@create']);
            $router->get('/bundles/{productId}/edit', ['as' => 'bundle.edit', 'uses' => 'BundleController@edit']);
            $router->put('/bundles/{productId}', ['as' => 'bundle.update', 'uses' => 'BundleController@update']);
            $router->post('/bundles', ['as' => 'bundle.store', 'uses' => 'BundleController@store']);

            /**
             * Site Controller
             */
            $router->resource('site', 'SiteController');

            /**
             * Company Controller
             */
            $router->resource('company', 'CompanyController');

            /**
             * Pixels Controller
             */
            $router->resource('pixel', 'PixelController');

            /**
             * Payment Settings Controller
             */
            $router->resource('payment-setting', 'PaymentSettingController');

            /**
             * Upsell Controller
             */
            $router->resource('upsell', 'UpsellController');

            /**
             * Additional Controller
             */
            $router->resource('additional', 'AdditionalController');

            /**
             * Roles/Permissions Controller
             */
            $router->resource('role', 'RoleController');
            $router->resource('permission', 'PermissionController');

            /**
             * Email Campaign Settings Controller
             */
            $router->resource('email-campaign-setting', 'EmailCampaignSettingController');
            /**
             * External Service Settings Controller
             */
            $router->resource('external-service-settings', 'ExternalServiceSettingsController');

            /**
             * ErpSetting Controller
             */
            $router->resource('erp-setting', 'ErpSettingController');

            /**
             * Product Link Routes
             */
            $router->resource('product-link', 'ProductLinkController');

            /**
             * Bundle Group Routes
             */
            $router->resource('bundle-group', 'BundleGroupController');

            /**
             * Commissions Config Routes
             */
            $router->resource('config-commission-group', 'ConfigCommissionGroupController');
            $router->resource('config-commission-rule', 'ConfigCommissionRuleController');

            /**
             * Sales Commissions
             */
            $router->resource('my-sales-commission', 'MySalesCommissionController');
            $router->resource('sales-commission', 'SalesCommissionController');
            $router->resource('paid-commission', 'PaidSalesCommissionController');

            /**
             * Update Session Sites
             */
            $router->post('/site/update-session', ['as' => 'site.updateSession', 'uses' => 'SiteController@updateSession']);

            /**
             * Generate Links
             */
            $router->get('/link-generator/create', ['as' => 'link-generator.create', 'uses' => 'LinkGeneratorController@create']);
            $router->post('/link-generator', ['as' => 'link-generator.store', 'uses' => 'LinkGeneratorController@store']);

            /**
             * PostBack Routes
             */
            $router->resource('post-back', 'PostBackController');

            /**
             * PostBack Routes
             */
            $router->resource('affiliate-pixel', 'AffiliatePixelController');

            /**
             * Currency Routes
             */
            $router->resource('currency', 'CurrencyController');

            /**
             * Api Key Routes
             */
            $router->resource('api-key', 'ApiKeyController');
        });

        /**
         * Profile Routes
         */
        $router->get('profile', ['as'=>'profile.edit', 'uses'=>'ProfileController@edit']);
        $router->put('profile', ['as'=>'profile.update', 'uses'=>'ProfileController@update']);

        /**
         * Common Ajax Routes
         */
        $router->controller('ajax', 'AjaxController');

        /**
         * Mails Routes
         */
        $router->get('/mail/resend-billet/{transaction_id}', ['as' => 'mail.resend-billet',  'uses' => 'MailController@resendBillet' ]);

        /**
         * Track Order Routes
         */
        $router->get('/track/orders/{customer_id}', ['as' => 'track.orders',  'uses' => 'TrackController@trackByCustomer' ]);
        $router->get('/track/order/{order_id}', ['as' => 'track.order',  'uses' => 'TrackController@trackByOrder' ]);

        /**
         * Log Viewer Routes
         */
        $router->get("/logger", ['as'=>'log-viewer.index', 'uses' => 'LogViewerController@index']);
        $router->get("/logger/logs", ['as'=>'log-viewer.logs', 'uses' => 'LogViewerController@listLogs']);
        $router->get("/logger/logs/{date}", ['as'=>'log-viewer.show', 'uses' => 'LogViewerController@show']);
        $router->get("/logger/filter/{date}/{level}", ['as'=>'log-viewer.filter', 'uses' => 'LogViewerController@showByLevel']);

        /**
         * Duplicate resources
         */
        $router->get('payment-setting/{id}/duplicate', [
            'as'  => 'payment-setting.duplicate', 'uses'=> 'PaymentSettingController@duplicate'
        ]);

        $router->get('site/{id}/duplicate', [
            'as'  => 'site.duplicate', 'uses'=> 'SiteController@duplicate'
        ]);
    });
});

/**
 * Notification Post Routes
 */
$router->group(['as' => 'notification::', 'prefix'=>'notifications'], function(Router $router){
    $router->post('asaas', ['as'=>'asaas', 'uses'=>'NotificationsController@asaas']);
    $router->post('boletofacil', ['as'=>'boletofacil', 'uses'=>'NotificationsController@boletofacil']);
    $router->any('pagseguro', ['as'=>'pagseguro', 'uses'=>'NotificationsController@pagseguro']);
    $router->post('mundipagg', ['as'=>'mundipagg', 'uses'=>'NotificationsController@mundipagg']);
});
