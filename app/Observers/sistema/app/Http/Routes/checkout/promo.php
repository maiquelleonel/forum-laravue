<?php

/**
 * @var \Illuminate\Routing\Router $router
 */

/**
 * Default promo exit page
 */
$router->get("/promo-exit", [
    'as' => 'checkout.exit', 'uses' => 'CheckoutController@promoExit'
])->middleware('anti_robot_access');

/**
 * Default promo exit page
 */
$router->get("/rebuy", [
    'as' => 'checkout.rebuy', 'uses' => 'CheckoutController@rebuy'
]);
