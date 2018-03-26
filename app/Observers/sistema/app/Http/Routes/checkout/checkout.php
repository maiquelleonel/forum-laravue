<?php

/**
 * @var \Illuminate\Routing\Router $router
 */

/**
 * Select Bundle before checkout
 */
$router->get("bundles", [
    'as' => 'select.bundle', 'uses' => 'BundleController@select'
]);

/**
 * Default checkout page
 */
$router->get("/", [
    'as' => 'checkout.index', 'uses' => 'CheckoutController@index'
])->middleware('anti_robot_access');

/**
 * Default Upsell page
 */
$router->get("upsell", [
    'as' => 'checkout.upsell', 'uses' => 'CheckoutController@upSell'
]);

/**
 * Default Additional page
 */
$router->get("additional", [
    'as' => 'checkout.additional', 'uses' => 'CheckoutController@additional'
]);

/**
 * Default Retry Page
 */
$router->get("retry", [
    'as' => 'checkout.retry', 'uses' => 'CheckoutController@retry'
])->middleware('anti_robot_access');
