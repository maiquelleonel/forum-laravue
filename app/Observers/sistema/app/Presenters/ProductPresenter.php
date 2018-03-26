<?php

namespace App\Presenters;

use Laracasts\Presenter\Presenter;

class ProductPresenter extends Presenter
{
    public function price()
    {
        return monetary_format( $this->entity->price );
    }

    public function ipi()
    {
        return number_format( $this->entity->ipi, 2, ',', '') . "%";
    }

    public function inventory()
    {
        return $this->entity->inventory ?: 'Sem Estoque';
    }
}