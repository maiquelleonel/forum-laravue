<?php

if (!function_exists("breadcrumbResource")) {
    function breadcrumbResource($resource, $parent = "admin:dashboard")
    {
        Breadcrumbs::register("admin:$resource.index", function ($breadcrumbs) use ($resource, $parent) {
            $breadcrumbs->parent($parent);
            $breadcrumbs->push(str_plural(humanize($resource)), route("admin:$resource.index"));
        });

        Breadcrumbs::register("admin:$resource.create", function ($breadcrumbs) use ($resource) {
            $breadcrumbs->parent("admin:$resource.index");
            $breadcrumbs->push(trans("button.create") . " " . humanize($resource), route("admin:$resource.create"));
        });

        Breadcrumbs::register("admin:$resource.edit", function ($breadcrumbs, $modelId) use ($resource) {
            $breadcrumbs->parent("admin:$resource.index");
            $breadcrumbs->push(trans("button.edit") . " " . humanize($resource), route("admin:$resource.edit", $modelId));
        });
    }
}

// Dashboard
Breadcrumbs::register('admin:dashboard', function ($breadcrumbs) {
    $breadcrumbs->push('Dashboard', route('admin:dashboard'));
});

// List Clients
Breadcrumbs::register('admin:customers.index', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.customers"), route('admin:customers.index'));
});

// List Clients (With delay)
Breadcrumbs::register('admin:delayed-customers.index', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.customers"), route('admin:delayed-customers.index'));
});

// List Canceled Clients
Breadcrumbs::register('admin:customers.canceled', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.customers_not_approved"), route('admin:customers.canceled'));
});

// List Canceled Clients (with delay)
Breadcrumbs::register('admin:delayed-customers.canceled', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.customers_not_approved_to_sales"), route('admin:delayed-customers.canceled'));
});

// List Interested Clients
Breadcrumbs::register('admin:customers.interested', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.interested_customers"), route('admin:customers.interested'));
});

// List Interested Clients (with delay)
Breadcrumbs::register('admin:delayed-customers.interested', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.interested_customers_to_sales"), route('admin:delayed-customers.interested'));
});

// Show Client
Breadcrumbs::register('admin:customers.show', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:customers.index');
    $breadcrumbs->push('Ver cliente');
});

// Create Client
Breadcrumbs::register('admin:customers.create', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:customers.index');
    $breadcrumbs->push(trans("menu.add_customer"), route('admin:customers.create'));
});

// Create Client
Breadcrumbs::register('admin:customers.edit', function ($breadcrumbs, $customerId) {
    $breadcrumbs->parent('admin:customers.index');
    $breadcrumbs->push(trans("button.edit") . ' ' . trans("menu.customers"), route('admin:customers.edit', $customerId));
});

// List Users
Breadcrumbs::register('admin:users.index', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.users"), route('admin:users.index'));
});

// List Users
Breadcrumbs::register('admin:deleted-users.index', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.inactive_users"), route('admin:deleted-users.index'));
});

// Edit User
Breadcrumbs::register('admin:users.edit', function ($breadcrumbs, $userId) {
    $breadcrumbs->parent('admin:users.index');
    $breadcrumbs->push(trans("button.edit") . ' ' . trans("menu.users"), route('admin:users.edit', $userId));
});

// Create User
Breadcrumbs::register('admin:users.create', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:users.index');
    $breadcrumbs->push(trans("button.create") . ' ' . trans("menu.users"), route('admin:users.create'));
});

// Reports
Breadcrumbs::register('admin:report.charts', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.charts"), route('admin:report.charts'));
});

Breadcrumbs::register('admin:report.vendors', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.sellers"), route('admin:report.vendors'));
});

Breadcrumbs::register('admin:report.bundles', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.bundles"), route('admin:report.bundles'));
});

Breadcrumbs::register('admin:report.comparative-tables', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.comparative_table"), route('admin:report.comparative-tables'));
});

Breadcrumbs::register('admin:report.evolux-login', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.report_evolux_login"), route('admin:report.evolux-login'));
});

Breadcrumbs::register('admin:report.extract-seller', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    if (auth()->user()) {
        $breadcrumbs->push(trans("menu.my_sales"), route('admin:report.extract-seller', auth()->user()->id));
    }
});

Breadcrumbs::register('admin:report.campaigns-orders', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.campaign_orders"), route('admin:report.campaigns-orders'));
});

Breadcrumbs::register('admin:report.campaigns-orders.details', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:report.campaigns-orders');
    $breadcrumbs->push(trans("menu.detailed_campaign_orders"), route('admin:report.campaigns-orders.details'));
});

Breadcrumbs::register('admin:report.campaigns-leads', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.campaign_leads"), route('admin:report.campaigns-leads'));
});

Breadcrumbs::register('admin:report.products-sold', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.products_sold"), route('admin:report.products-sold'));
});

Breadcrumbs::register('admin:report.bundles-sold', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.bundles_sold"), route('admin:report.bundles-sold'));
});

//Billing
Breadcrumbs::register('admin:billing.campaign-costs.index', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.campaign_costs"), route('admin:billing.campaign-costs.index'));
});

//List Orders
Breadcrumbs::register('admin:orders.index', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.orders"), route('admin:orders.index', [
        'orderBy' => 'id',
        'sortedBy'=> 'desc'
    ]));
});

//List Orders without Invoice
Breadcrumbs::register('admin:orders.without-invoice', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.orders_waiting_invoice"), route('admin:orders.without-invoice', [
        'orderBy' => 'id',
        'sortedBy'=> 'desc'
    ]));
});

//List Orders without Invoice
Breadcrumbs::register('admin:orders.update-invoice-number', function ($breadcrumbs, $orderId) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push('update-invoice-number', route('admin:orders.update-invoice-number', $orderId));
});

//Show Order
Breadcrumbs::register('admin:orders.show', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:orders.index');
    $breadcrumbs->push('Ver pedido');
});

// Produtos
Breadcrumbs::register('admin:product.index', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.products"), route('admin:product.index'));
});

Breadcrumbs::register('admin:product.edit', function ($breadcrumbs, $product) {
    $breadcrumbs->parent('admin:product.index');
    $breadcrumbs->push(trans("button.edit") . ' ' . trans("menu.products"), route('admin:product.edit', $product));
});

Breadcrumbs::register('admin:product.create', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:product.index');
    $breadcrumbs->push(trans("button.create") . ' ' . trans("menu.products"), route('admin:product.create'));
});


// Bundles
Breadcrumbs::register('admin:bundle.index', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("menu.bundles"), route('admin:bundle.index'));
});

Breadcrumbs::register('admin:bundle.edit', function ($breadcrumbs, $product) {
    $breadcrumbs->parent('admin:bundle.index');
    $breadcrumbs->push(trans("button.edit") . ' ' . trans("menu.bundles"), route('admin:bundle.edit', $product));
});

Breadcrumbs::register('admin:bundle.create', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:bundle.index');
    $breadcrumbs->push(trans("button.edit") . ' ' . trans("menu.bundles"), route('admin:bundle.create'));
});

// My profile
Breadcrumbs::register('admin:profile.edit', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("profile.edit_my_profile"), route('admin:profile.edit'));
});

// Log Viewer
Breadcrumbs::register('admin:log-viewer.index', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push('Estatísticas', route('admin:log-viewer.index'));
});

Breadcrumbs::register('admin:log-viewer.logs', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push('Logs', route('admin:log-viewer.logs'));
});

Breadcrumbs::register('admin:log-viewer.show', function ($breadcrumbs, $date) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push('Logs', route('admin:log-viewer.show', [$date]));
});

Breadcrumbs::register('admin:log-viewer.filter', function ($breadcrumbs, $date, $level) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push('Logs', route('admin:log-viewer.filter', [$date, $level]));
});

/**
 * Link Generator
 */
Breadcrumbs::register('admin:link-generator.create', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:dashboard');
    $breadcrumbs->push(trans("validation.attributes.link_generator"), route('admin:link-generator.create'));
});

Breadcrumbs::register('admin:link-generator.store', function ($breadcrumbs) {
    $breadcrumbs->parent('admin:link-generator.create');
    $breadcrumbs->push(trans("validation.attributes.link_generator"), route('admin:link-generator.store'));
});

breadcrumbResource("site");
breadcrumbResource("pixel");
breadcrumbResource("payment-setting");
breadcrumbResource("company");
breadcrumbResource("upsell");
breadcrumbResource("role");
breadcrumbResource("permission");
breadcrumbResource("email-campaign-setting");
breadcrumbResource("external-service-settings");
breadcrumbResource("erp-setting");
breadcrumbResource("product-link");
breadcrumbResource("bundle-group");
breadcrumbResource("additional");
breadcrumbResource("config-commission-group");
breadcrumbResource("config-commission-rule", "admin:config-commission-group.index");
breadcrumbResource("my-sales-commission");
breadcrumbResource("sales-commission");
breadcrumbResource("post-back");
breadcrumbResource("paid-commission");
breadcrumbResource("currency");
breadcrumbResource("affiliate-pixel");
breadcrumbResource("api-key");
