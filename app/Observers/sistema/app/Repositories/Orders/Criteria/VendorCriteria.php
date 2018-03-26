<?php

namespace App\Repositories\Orders\Criteria;

use App\Order;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class VendorCriteria implements CriteriaInterface
{

    /**
     * @var string vendor
     */
    private $vendor;

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
        if($this->vendor) {
            return $model->where('user_id', $this->vendor);
        }
        return $model;
    }
}