<?php

namespace App\Presenters;

use Laracasts\Presenter\Presenter;

class UserPresenter  extends Presenter
{
    public function firstname()
    {
        return explode(" ", $this->entity->name)[0];
    }

    public function memberSince()
    {
        return $this->created_at->format('d/m/Y');
    }

    public function lastUpdate()
    {
        return $this->updated_at->format('d/m/Y H:m:i');
    }

    public function inactiveOn()
    {
        return $this->deleted_at->format('d/m/Y H:m:i');
    }

    public function displayName()
    {
        return mb_strtoupper($this->name);
    }

    public function avatar($size = 100)
    {
        $hash = md5($this->email);
        return "https://www.gravatar.com/avatar/$hash?s=$size";
    }

}