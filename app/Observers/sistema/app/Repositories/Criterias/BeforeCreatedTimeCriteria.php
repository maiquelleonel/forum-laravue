<?php

namespace App\Repositories\Criterias;

use Carbon\Carbon;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class BeforeCreatedTimeCriteria implements CriteriaInterface
{

    /**
     * @var Carbon $createdAt
     */
    private $createdAt;

    public function __construct(Carbon $createdAt)
    {
        $this->createdAt = $createdAt;
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
        if($this->createdAt) {
            return $model->where('created_at', "<=", $this->createdAt);
        }
        return $model;
    }
}