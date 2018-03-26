<?php

namespace App\Presenters;

use App\Support\MobileDetect;
use Laracasts\Presenter\Presenter;
use App\Services\Payment\Response\CreditCard;

/**
 * Class CampaignCostPresenter
 *
 * @package namespace App\Presenters;
 */
class CampaignCostPresenter extends Presenter
{
    public function costOfDay()
    {
        return monetary_format($this->entity->cost ?: 0);
    }
}