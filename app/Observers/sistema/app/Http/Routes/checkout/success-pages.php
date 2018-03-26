<?php

/**
 * @var \Illuminate\Routing\Router $router
 */

/**
 * Default Router Success Page
 */
$router->get("success", [
    'as' => 'successPage', 'uses' => 'SuccessPagesController@successPage'
]);

/**
 * Credit Card Success Page
 */
$router->get("success-creditcard", [
    'as' => 'success.creditCard', 'uses' => 'SuccessPagesController@creditCard'
]);

/**
 * Boleto Success Page
 */
$router->get("success-boleto", [
    'as' => 'success.boleto', 'uses' => 'SuccessPagesController@boleto'
]);

/**
 * PagSeguro Success Page
 */
$router->get("success-pagseguro", [
    'as' => 'success.pagSeguro', 'uses' => 'SuccessPagesController@pagSeguro'
]);

/**
 * PayPal Success Page
 */
$router->get("success-paypal", [
    'as' => 'success.payPal', 'uses' => 'SuccessPagesController@payPal'
]);