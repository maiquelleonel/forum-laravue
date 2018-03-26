<?php

namespace App\Presenters;
use Pingpong\Menus\MenuItem;
use Pingpong\Menus\Presenters\Bootstrap\NavbarPresenter;

class SidebarMenuPresenter extends NavbarPresenter
{
    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * @var object
     */
    private $parsedUrl;

    /**
     * SidebarMenuPresenter constructor.
     */
    public function __construct()
    {
        $this->request = request();
        $this->parsedUrl = (object) parse_url( $this->request->url() );
    }

    /**
     * {@inheritdoc}
     */
    public function getOpenTagWrapper()
    {
        return  PHP_EOL.'<ul class="sidebar-menu">'.PHP_EOL;
    }
    /**
     * {@inheritdoc}
     */
    public function getMenuWithDropDownWrapper($item)
    {
        if ($this->dropdownHasPermission( $item )) {
            $pendencies = $this->dropdownHasPendencies( $item );
            if($pendencies){
                $pendencies = ' <i class="fa fa-exclamation-triangle text-yellow"></i>';
            }

            return '
                <li class="treeview '.$this->hasActiveChild($item->getChilds()).'">
                    <a href="#">
                        '.$item->getIcon().'
                        <span>'.$item->title.$pendencies.'</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        '.$this->getChildMenuItems($item).'
                    </ul>
                </li>';
        }
    }

    /**
     * @param array $items
     * @return string
     */
    private function hasActiveChild($items)
    {
        /**
         * @var MenuItem $item
         */
        foreach ($items as $item) {
            if( $this->isActive( $item ) ) {
                return "active";
            }
        }

        return "";
    }

    /**
     * @param MenuItem $item
     * @return bool
     */
    private function isActive(MenuItem $item)
    {
        $parsedUrl = (object) parse_url( $item->getUrl() );
        if( isset($parsedUrl->path, $this->parsedUrl->path) && $parsedUrl->path == $this->parsedUrl->path ) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc }.
     */
    public function getMenuWithoutDropdownWrapper($item)
    {
        if( $this->itemHasPermission( $item ) ) {
            $active = $this->isActive( $item ) ? " class='active' " : "";
            return '<li'.$active.'><a href="'.$item->getUrl().'" '.$item->getAttributes().'>'.$item->getIcon().' <span>'.$item->title.'</span></a></li>'.PHP_EOL;
        }
    }

    public function dropdownHasPermission(MenuItem $item)
    {
        foreach ($item->getChilds() as $child) {
            if ($this->itemHasPermission( $child )) {
                return true;
            }
        }

        return false;
    }

    public function itemHasPermission($item)
    {
        return isset( $item->route[0] ) && auth()->user()->hasPermission($item->route[0]);
    }

    private function dropdownHasPendencies($item)
    {
        foreach ($item->getChilds() as $child) {
            if ($this->itemHasPendencies( $child )) {
                return true;
            }
        }

        return false;
    }

    public function itemHasPendencies($item)
    {
        return str_contains($item->title, "pull-right-container");
    }
}