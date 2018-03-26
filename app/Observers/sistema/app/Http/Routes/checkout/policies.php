<?php

/**
 * @var \Illuminate\Routing\Router $router
 */

/**
 * Privacy Policy
 */
$router->get("privacy", ['as' => 'privacy', function(){
    return view('policy::privacy');
}]);

/**
 * Exchange Policy
 */
$router->get("return", ['as' => 'return', function(){
    return view('policy::return');
}]);