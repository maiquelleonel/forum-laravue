<?php

namespace App\Repositories\Transaction\Criteria;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class TransactionVendorCriteria implements CriteriaInterface
{
    /**
     * @var
     */
    private $vendor;

    /**
     * TransactionTypeCriteria constructor.
     * @param $vendor
     */
    public function __construct($vendor)
    {
        $this->vendor = $vendor;
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
        if ($vendor = $this->vendor) {
            return $model->whereHas('order', function($query) use ($vendor){
                return $query->where('user_id', $vendor);
            });
        }

        return $model;
    }
}
