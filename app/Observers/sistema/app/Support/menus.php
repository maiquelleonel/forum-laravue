<?php

use App\Domain\OrderStatus;
use App\Entities\Order;
use App\Repositories\Criterias\JustSitesInSessionCriteria;
use App\Repositories\Orders\Criteria\StatusCriteria;
use App\Repositories\Orders\OrderRepository;

Menu::create('sidebar', function ($menu) {
    $menu->setPresenter(App\Presenters\SidebarMenuPresenter::class);

    $menu->route('admin:dashboard', trans("menu.dashboard"), [], 0, ['icon' => 'fa fa-dashboard']);

    $menu->dropdown(trans("menu.customers"), function ($sub) {
        $sorter = ['orderBy' => 'id', 'sortedBy'=> 'desc'];
        $sub->route('admin:customers.index', trans("menu.all_customers"), $sorter);
        $sub->route('admin:customers.canceled', trans("menu.customers_not_approved"), $sorter);
        $sub->route('admin:customers.interested', trans("menu.interested_customers"), $sorter);

        $sub->route('admin:delayed-customers.index', trans("menu.all_customers_to_sales"), $sorter);
        $sub->route('admin:delayed-customers.canceled', trans("menu.customers_not_approved_to_sales"), $sorter);
        $sub->route('admin:delayed-customers.interested', trans("menu.interested_customers_to_sales"), $sorter);

        $sub->route('admin:customers.create', trans("menu.add_customer"));
    }, 1, ['icon' => 'fa fa-address-card']);

    $menu->dropdown(trans("menu.orders"), function ($sub) {
        $sub->route('admin:orders.index', trans("menu.all_orders"), ['orderBy' => 'id', 'sortedBy'=> 'desc']);
        $sub->route('admin:orders.without-invoice', trans("menu.orders_waiting_invoice"), [
            'orderBy' => 'id', 'sortedBy'=> 'desc']);
        if (auth()->user() && auth()->user()->hasPermission("admin:orders.index")) {
            $counter = null;
            $count = app(OrderRepository::class)
                        ->pushCriteria(new JustSitesInSessionCriteria())
                        ->pushCriteria(new StatusCriteria([OrderStatus::PENDING_INTEGRATION]))
                        ->count();

            if ($count > 0) {
                $counter = '</span><span class="label label-danger pull-right">'.
                $count.'</span><span>';
            }

            $sub->route('admin:orders.index', trans("menu.orders_pending_review") . $counter, [
                'orderBy' => 'id', 'sortedBy'=> 'desc', 'status'=> [ OrderStatus::PENDING_INTEGRATION ]
            ]);

            $counter = null;
            $count = app(OrderRepository::class)
                        ->pushCriteria(new JustSitesInSessionCriteria())
                        ->pushCriteria(new StatusCriteria([OrderStatus::PENDING_INTEGRATION_IN_ANALYZE]))
                        ->count();

            if ($count > 0) {
                $counter ='</span><span class="label label-danger pull-right">'.
                $count.'</span><span>';
            }

            $sub->route('admin:orders.index', trans("menu.orders_pending_but_in_review") . $counter, [
                'orderBy' => 'id', 'sortedBy'=> 'desc', 'status'=> [OrderStatus::PENDING_INTEGRATION_IN_ANALYZE]
            ]);
        }
    }, 2, ['icon' => 'fa fa-shopping-cart']);

    $menu->dropdown(trans("menu.users_and_permissions"), function ($sub) {
        $sub->route('admin:users.index', trans("menu.users"));
        $sub->route('admin:role.index', trans("menu.roles"));
        $sub->route('admin:permission.index', trans("menu.permissions"));
    }, 4, ['icon' => 'fa fa-users']);

    $menu->dropdown(trans("menu.revenues"), function ($sub) {
        $sub->route('admin:billing.campaign-costs.index', trans("menu.campaign_costs"));
        $sub->route('admin:config-commission-group.index', trans("menu.commission_groups"));
        $sub->route('admin:sales-commission.index', trans("menu.sales_commission"));
        $sub->route('admin:paid-commission.index', trans("menu.paid_commission"));
    }, 1, ['icon' => 'fa fa-money']);

    $menu->dropdown(trans("menu.reports"), function ($sub) {
        if (auth()->check()) {
            $sub->route('admin:report.extract-seller', trans("menu.my_sales"), [auth()->user()->id]);
        }
        $sub->route('admin:my-sales-commission.index', trans("menu.my_commissions"));
        $sub->route('admin:report.charts', trans("menu.charts"));
        $sub->route('admin:report.vendors', trans("menu.sellers"));
        $sub->route('admin:report.products-sold', trans("menu.products_sold"));
        $sub->route('admin:report.bundles-sold', trans("menu.bundles_sold"));
        $sub->route('admin:report.comparative-tables', trans("menu.comparative_table"));
        $sub->route('admin:report.campaigns-orders', trans("menu.campaign_orders"));
        $sub->route('admin:report.campaigns-leads', trans("menu.campaign_leads"));
        $sub->route('admin:report.evolux-login', trans("menu.report_evolux_login"));
    }, 1, ['icon' => 'fa fa-line-chart']);

    $menu->dropdown(trans("menu.catalog"), function ($sub) {
        $sub->route("admin:product.index", trans("menu.products"));
        $sub->route("admin:bundle.index", trans("menu.bundles"));
        $sub->route("admin:upsell.index", trans("menu.upsell"));
        $sub->route("admin:additional.index", trans("menu.additional"));
        $sub->route("admin:product-link.index", trans("menu.product_links"));
        $sub->route("admin:bundle-group.index", trans("menu.bundle_group"));
    }, 3, ['icon' => 'fa fa-tags']);


    $menu->dropdown(trans("menu.pixels_and_postback"), function ($sub) {
        $sub->route("admin:pixel.index", trans("menu.pixels"));
        $sub->route("admin:post-back.index", trans("menu.url_post_back"));
        $sub->route("admin:affiliate-pixel.index", trans("menu.affiliate_pixel"));
    }, 4, ['icon' => 'fa fa-share-alt-square']);

    $menu->dropdown(trans("menu.settings"), function ($sub) {
        $sub->route("admin:site.index", trans("menu.sites"));
        $sub->route("admin:payment-setting.index", trans("menu.payment_setting"));
        $sub->route("admin:company.index", trans("menu.company_setting"));
        $sub->route("admin:erp-setting.index", trans("menu.erp_integration_setting"));
        $sub->route("admin:email-campaign-setting.index", trans("menu.email_campaign_setting"));
        $sub->route("admin:external-service-settings.index", trans("menu.external_service_setting"));
        $sub->route("admin:link-generator.create", trans("menu.link_generator"));
        $sub->route("admin:currency.index", trans("menu.currencies"));
        $sub->route("admin:api-key.index", trans("menu.api_key"));
    }, 3, ['icon' => 'fa fa-cog']);

    $menu->route("admin:log-viewer.index", trans("menu.logs"), [], ['icon'=>'fa fa-tasks']);
});
