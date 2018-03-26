<?php

namespace App\Repositories\Orders\Criteria;

use App\Entities\Customer;
use App\Entities\SalesCommission;
use App\Order;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class SiteCriteria implements CriteriaInterface
{

    /**
     * @var array offers
     */
    private $offers;

    public function __construct($offers = [])
    {
        $this->offers  = $offers;
    }

    /**
     * Apply criteria in query repository
     *
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        if($this->offers && count($this->offers) > 0){
            if($model->getModel() instanceof SalesCommission){
                $model = $model->join("orders as site_order", "site_order.id", "=", "sales_commission.order_id")
                               ->join("customers as site_customer", "site_customer.id", "=", "site_order.customer_id")
                               ->whereIn("site_customer.site_id", $this->offers);
            }

            if($model->getmodel() instanceof Customer){
                $model = $model->whereIn("customers.site_id", $this->offers);
            }
        }
        return $model;
    }
}