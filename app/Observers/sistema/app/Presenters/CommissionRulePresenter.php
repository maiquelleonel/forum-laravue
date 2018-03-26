<?php

namespace App\Presenters;

use App\Entities\ConfigCommissionRule;
use Laracasts\Presenter\Presenter;

class CommissionRulePresenter extends Presenter
{
    public function type()
    {
        return $this->entity->type == ConfigCommissionRule::TYPE_PERCENTAGE
                ? "Percentual"
                : "Valor Fixo";
    }

    public function value()
    {
        return $this->entity->type == ConfigCommissionRule::TYPE_PERCENTAGE
            ? $this->entity->value . "%"
            : monetary_format($this->entity->value, $this->entity->currency->code);
    }

    public function shaving_rate()
    {
        return $this->entity->shaving_rate
            ? $this->entity->shaving_rate . "%"
            : "100%";
    }
}