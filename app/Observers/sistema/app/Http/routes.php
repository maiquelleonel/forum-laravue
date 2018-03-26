<?php

/**
 * @var \Illuminate\Routing\Router $router
 */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/**
 * Admin Routes
 */
if (env("ADMIN_DOMAIN")) {
    $router->group(["domain"=>env("ADMIN_DOMAIN"), "namespace"=>"Admin"], function ($router) {
        include __DIR__ . "/Routes/admin.php";
    });

    $router->group([
        "domain" => env("ADMIN_DOMAIN"),
        "namespace" => "Api",
        "middleware"=> [
            "auth.api",
            "change_user_locale"
        ]], function ($router) {
            include __DIR__ . "/Routes/api-v1.php";
        });
}

if (explode(":", \Request::server("HTTP_HOST"))[0] != env("ADMIN_DOMAIN")) {
    include __DIR__ . "/Routes/landing-pages.php";

    $router->group([
        "namespace"  => 'Site',
        'middleware' => [
            //'redirect_to_rt_pages',
            'validate_click_id',
            'insert_gtm',
            'insert_tracking_pixel'
        ]], function ($router) {
            include __DIR__ . "/Routes/sites.php";
        });

    $router->group([
        "namespace" => "Site",
        "prefix"    => config("routing.version_prefix"),
        'middleware' => [
            //'redirect_to_rt_pages',
            'validate_click_id',
            'insert_gtm',
            'insert_tracking_pixel'
        ]], function ($router) {
            include __DIR__ . "/Routes/sites.php";
        });
}
