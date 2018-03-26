<?php
/**
 * @var \Illuminate\Routing\Router $router
 */

/**
 * Display form to Login
 */
$router->get("/", [
    'as' => 'index', 'uses' => 'TrackController@index'
]);

/**
 * Display orders from customer
 */
$router->get("orders", [
    'as' => 'orders', 'uses' => 'TrackController@orders'
]);

/**
 * Display track data from order
 */
$router->get("order/{orderid}", [
    'as' => 'order', 'uses' => 'TrackController@track'
]);