<?php

/**
 * @var \Illuminate\Routing\Router $router
 */

/**
 * CreditCard Payment
 */
$router->post("creditcard", [
    'as'         => 'payment.creditcard',
    'uses'       => 'PaymentController@creditCard',
    'middleware' => [
        'anti_robot_access',
        'block_by_many_credit_cards',
    ]
]);

/**
 * CreditCard Upsell Payment
 */
$router->post("creditcard-upsell", [
    'as' => 'payment.upsell', 'uses' => 'PaymentController@creditCardUpSell'
]);

/**
 * CreditCard Additional Payment
 */
$router->post("creditcard-additonal", [
    'as' => 'payment.additional', 'uses' => 'PaymentController@creditCardAdditional'
]);

/**
 * Boleto Payment
 */
$router->post("boleto", [
    'as' => 'payment.boleto', 'uses' => 'PaymentController@boleto'
]);

/**
 * PagSeguro Payment
 */
$router->post("pagseguro", [
    'as' => 'payment.pagseguro', 'uses' => 'PaymentController@pagSeguro'
]);

/**
 * PayPal Payment
 */
$router->post("paypal", [
    'as' => 'payment.paypal', 'uses' => 'PaymentController@payPal'
]);

$router->any("paypal/confirm", [
    'as' => 'payment.paypal.confirm', 'uses' => 'PaymentController@confirmPayPal'
]);
