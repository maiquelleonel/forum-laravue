<?php

namespace App\Presenters;

class UpsellPresenter extends ProductPresenter
{
    public function installmentValue()
    {
        return installments($this->entity->price, $this->entity->installments);
    }

    public function installmentMoney()
    {
        return monetary_format($this->price/$this->installments);
    }

    public function quantity()
    {
        return $this->qty * $this->bundle->qty;
    }
}