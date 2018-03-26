<?php

/**
 * Api V1 Routes
 * @var \Illuminate\Routing\Router $router
 */
$router->group(["prefix"=>"api/v1", "namespace"=>"V1"], function($router){
    /**
     * @var \Illuminate\Routing\Router $router
     */

    /**
     * Customer Routes
     */
    $router->resource("customer", "CustomerController", [ "only" => [
        "index", "show", "store", "update"
    ]]);

    /**
     * Order Routes
     */
    $router->resource("order", "OrderController", [ "only" => [
        "index", "show", "store", "update"
    ]]);

    /**
     * Payment Routes
     */
    $router->post("payment/credit-card", ["as"=>"payment.credit-card", "uses"=>"PaymentController@creditCard"]);
    $router->post("payment/boleto",      ["as"=>"payment.boleto",      "uses"=>"PaymentController@boleto"]);

});