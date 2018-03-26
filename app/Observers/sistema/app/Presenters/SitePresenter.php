<?php

namespace App\Presenters;

/**
 * Class SitePresenter
 *
 * @package namespace App\Presenters;
 */
class SitePresenter extends BasePresenter
{
    public function icon($icon = "globe")
    {
        return "<i class='fa fa-{$icon}' style='color: " . $this->entity->color . "'></i>";
    }

    public function nameWithIcon()
    {
        return "<strong style='color: {$this->entity->color} '>"
                    . $this->icon() . " " . $this->entity->name .
                "</strong>";
    }

    public function nameWithColor()
    {
        return $this->icon("square") . " " . $this->entity->name;
    }
}



/**
 * $icons = "";

foreach (\App\Entities\Site::all() as $site) {
$icons .= $site->present()->icon . " ";
}

$menu->url("#", $icons);
 */