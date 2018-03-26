<?php

if(!function_exists("pixel")){
    /**
     * Print pixel by name
     * @param $site
     * @param $pixelName
     * @return null
     */
    function pixel($site, $pixelName)
    {
        return isset($site->pixel->{$pixelName})
                ? $site->pixel->{$pixelName}
                : null;

    }
}

if(!function_exists("affiliate_pixel")){
    /**
     * Print affiliate pixel by site
     * @param $site
     * @param $page
     * @return null
     */
    function affiliate_pixel($site, $page)
    {
        $route = route("site::affiliate-pixel", $page);
        return "<iframe src='{$route}' style='width: 1px;height: 1px;' frameborder='0'></iframe>";
    }
}