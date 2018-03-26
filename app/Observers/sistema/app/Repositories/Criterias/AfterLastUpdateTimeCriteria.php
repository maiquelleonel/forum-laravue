<?php

namespace App\Repositories\Criterias;

use Carbon\Carbon;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class AfterLastUpdateTimeCriteria implements CriteriaInterface
{

    /**
     * @var Carbon $lastUpdate
     */
    private $lastUpdate;

    public function __construct(Carbon $lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;
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
        if($this->lastUpdate) {
            return $model->where('updated_at', ">=", $this->lastUpdate);
        }
        return $model;
    }
}