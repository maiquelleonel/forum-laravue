<?php
/**
* @var \Illuminate\Routing\Router $router
*/

/**
 * Form Create Customer
 */
$router->get("customer", [
    'as' => 'customer.create', 'uses' => 'CustomerController@create'
]);

/**
* Update Customer
*/
$router->put("customer", [
    'as' => 'customer.update', 'uses' => 'CustomerController@update'
]);

/**
 * Create Customer
 */
$router->post("customer", [
    'as' => 'customer.store', 'uses' => 'CustomerController@store'
]);

/**
 * Create Customer
 */
$router->post("customer/offer", [
    'as' => 'customer.store-offer', 'uses' => 'CustomerController@storeFromExternalLP'
]);
