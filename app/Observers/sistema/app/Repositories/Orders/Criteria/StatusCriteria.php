<?php

namespace App\Repositories\Orders\Criteria;

use App\Order;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class StatusCriteria implements CriteriaInterface
{

    /**
     * @var status
     */
    private $status;

    public function __construct(array $status)
    {
        $this->status = $status;
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
        if($this->status) {
            return $model->whereIn('status', $this->status);
        }
        return $model;
    }
}