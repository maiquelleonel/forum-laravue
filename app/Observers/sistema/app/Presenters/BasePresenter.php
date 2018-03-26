<?php

namespace App\Presenters;

use Laracasts\Presenter\Presenter;

abstract class BasePresenter extends Presenter
{
    public function createdAt()
    {
        return $this->entity->created_at->format('d/m/Y H\hi');
    }

    public function updatedAt()
    {
        return $this->entity->updated_at->format('d/m/Y H\hi');
    }
}