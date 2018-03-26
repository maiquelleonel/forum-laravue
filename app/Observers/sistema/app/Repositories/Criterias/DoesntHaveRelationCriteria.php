<?php

namespace App\Repositories\Criterias;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class DoesntHaveRelationCriteria implements CriteriaInterface
{

    /**
     * @var string
     */
    private $relation;

    /**
     * DoesntHaveRelationCriteria constructor.
     * @param string $relation
     */
    public function __construct($relation)
    {
        $this->relation = $relation;
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
        if($this->relation) {
            return $model->doesntHave($this->relation);
        }
        return $model;
    }
}