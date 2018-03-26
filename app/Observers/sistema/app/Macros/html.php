<?php

Html::macro('routeUrl', function ($routeNameOrUrl, $data = []){
    if (Route::has($routeNameOrUrl)) {
        return route($routeNameOrUrl, $data);
    }
    return url($routeNameOrUrl);
});

Html::macro("faIcon", function($iconName, $size = null, $color = null){

    if ($size) {
        $size = "font-size: {$size}pt;";
    }
    if ($color) {
        $color = "color: {$color};";
    }

    return "<span class='fa fa-$iconName' style='{$size}{$color}'></span>";
});

Html::macro("uList", function ($array, Closure $callback = null) {
    $html = "<ul>";

    foreach ($array as $item) {
        $html.= "<li>";
        if (is_callable($callback)) {
            $html .= $callback( $item );
        } else {
            $html.= $item;
        }
        $html.= "</li>";
    }

    return $html."</ul>";

});

Html::macro("strong", function ($string) {
    return "<strong>{$string}</strong>";
});