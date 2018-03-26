<?php

namespace App\Repositories\SalesCommission\Criteria;

use App\Order;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class CommissionStatusCriteria implements CriteriaInterface
{

    /**
     * @var string type
     */
    private $type;

    public function __construct($type)
    {
        $this->type = $type;
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
        if($this->type) {
            if(is_array($this->type)){
                return $model->whereIn('sales_commission.status', $this->type);
            }
            return $model->where('sales_commission.status', $this->type);
        }
        return $model;
    }
}