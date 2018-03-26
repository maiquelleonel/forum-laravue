<?php

namespace App\Repositories\Criterias;

use Carbon\Carbon;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class BeforeLastUpdateTimeCriteria implements CriteriaInterface
{

    /**
     * @var Carbon $lastUpdate
     */
    private $lastUpdate;

    /**
     * @var string
     */
    private $dateField;

    /**
     * BeforeLastUpdateTimeCriteria constructor.
     * @param Carbon $lastUpdate
     * @param string $dateField
     */
    public function __construct(Carbon $lastUpdate, $dateField = "updated_at")
    {
        $this->lastUpdate = $lastUpdate;
        $this->dateField  = $dateField;
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
            return $model->where($this->dateField, "<=", $this->lastUpdate);
        }
        return $model;
    }
}