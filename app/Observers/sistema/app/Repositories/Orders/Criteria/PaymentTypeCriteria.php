<?php

namespace App\Repositories\Orders\Criteria;

use App\Entities\SalesCommission;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class PaymentTypeCriteria implements CriteriaInterface
{

    /**
     * @var string paymentType
     */
    private $paymentType;

    public function __construct($paymentType)
    {
        $this->paymentType = $paymentType;
    }

    /**
     * Apply criteria in query repository
     *
     * @param Builder $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        if($this->paymentType){
            if ($model->getModel() instanceof SalesCommission) {
                $model = $model->join("orders", "orders.id", "=", "sales_commission.order_id");
            }
        }

        return $this->applyConditions($model);
    }

    private function applyConditions($model)
    {
        if(is_array($this->paymentType)) {
            return $model->whereIn('orders.payment_type_collection', $this->paymentType);
        } else if ($this->paymentType) {
            return $model->where('orders.payment_type_collection', $this->paymentType);
        } if (is_null($this->paymentType)) {
            return $model->whereNull('orders.payment_type_collection');
        }

        return $model;
    }
}