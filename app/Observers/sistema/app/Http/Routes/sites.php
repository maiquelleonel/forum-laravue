<?php

/**
 * @var \Illuminate\Routing\Router $router
 */

/**
 * @var $site \App\Support\SiteSettings
 */
$site = app(\App\Support\SiteSettings::class);

$router->get(config('tracking.iframe_route'), function(){});

/**
 * Home Page
 */
if ($site->isRemarketing()) {
    $router->get('/', ['as' => 'home', 'uses'=>'CheckoutController@remarketing']);
} else {
    $router->get('/', ['as' => 'home', 'uses'=>'PagesController@home', 'middleware' => 'redirect_to_rt_pages']);
}

/**
 * Track Routes
 */
$router->group(['prefix' => 'track', 'as' => 'track::'], function ($router) {
    require __DIR__ . '/checkout/track.php';
});

/**
 * Policies Routes
 */
$router->group(['prefix' => 'policy', 'as' => 'policy::'], function ($router) {
    require __DIR__ .'/checkout/policies.php';
});


/**
 * Customer Routes
 */
$router->group(['prefix' => "customer", 'as' => 'checkout::'], function ($router) {
    require __DIR__ . '/checkout/customer.php';
});

/**
 * Payment Routes
 */
$router->group(['middleware'=> ['order.prevent-duplication', 'prevent.back'], 'as'=> 'checkout::'], function ($router) use ($site) {

    if (!$site->isRemarketing()) {
        require __DIR__ . '/checkout/promo.php';
    }

    $router->group(['middleware'=> ['customer.canBuy'], 'prefix'=> "order"], function($router){
        require __DIR__ . '/checkout/checkout.php';
        require __DIR__ . '/checkout/payment.php';
    });
});

/**
 * Thank You Routes
 */
$router->group(['prefix' => "order", 'middleware'=> ['customer.canBuy', 'prevent.back'], 'as' => 'checkout::'], function ($router) {
    require __DIR__ . '/checkout/success-pages.php';
});

$router->get("pixel/{page}", ["as"=>"site::affiliate-pixel", "uses"=>"AffiliatePixelController@embeddedPixel"]);
