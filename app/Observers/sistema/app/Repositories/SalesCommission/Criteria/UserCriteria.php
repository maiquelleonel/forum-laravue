<?php

namespace App\Repositories\SalesCommission\Criteria;

use App\Order;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class UserCriteria implements CriteriaInterface
{

    /**
     * @var string user
     */
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
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
        if($this->user) {
            return $model->where($model->getModel()->getTable().'.user_id', $this->user);
        }
        return $model;
    }
}