<?php

/**
 * @var \Illuminate\Routing\Router $router
 */

foreach (config("landing-pages") as $landingPage) {
    foreach( $landingPage->domains as $domain ) {
        $router->group(["domain" => $domain ], function($router) use($landingPage){
            $router->controller($landingPage->prefix, "LandingPages\\" . $landingPage->controller);
        });
    }
}