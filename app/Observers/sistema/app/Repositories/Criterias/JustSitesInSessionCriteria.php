<?php

namespace App\Repositories\Criterias;

use App\Entities\Bundle;
use App\Entities\Customer;
use App\Entities\Order;
use App\Entities\Transaction;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class JustSitesInSessionCriteria implements CriteriaInterface
{

    /**
     * Apply criteria in query repository
     *
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $sites = \Cookie::get("sites", []);

        switch (get_class($model->getModel())) {
            case Order::class:
                return $this->applyByOrder($model, $sites);

            case Customer::class:
                return $this->applyByCustomer($model, $sites);

            case Transaction::class:
                return $this->applyByTransaction($model, $sites);

            case Bundle::class:
                return $this->applyByBundle($model, $sites);
        }

        return $model;
    }

    private function applyByOrder($model, $sites)
    {
        return $model->whereHas("customer", function($query) use ($sites){
            return $query->whereIn("site_id", $sites);
        });
    }

    private function applyByCustomer($model, $sites)
    {
        return $model->whereIn("site_id", $sites);
    }

    private function applyByTransaction($model, $sites)
    {
        return $model->whereHas("order.customer", function($query) use ($sites){
            return $query->whereIn("site_id", $sites);
        });
    }

    private function applyByBundle($model, $sites)
    {
        return $model->whereHas("sites", function($query) use($sites){
            return $query->whereIn("site.id", $sites);
        });
    }
}