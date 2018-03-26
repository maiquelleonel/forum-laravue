<?php

namespace App\Repositories\Orders\Criteria;

use App\Order;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class PendingCriteria implements CriteriaInterface
{

    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
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
        if($this->request->has('pending')) {
            return $model->whereHas('customer', function($customer){
                return $customer->where('status', '');
            })->orWhere('status', Order::STATUS_CANCELED);
        }
        return $model;
    }
}