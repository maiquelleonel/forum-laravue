<?php

namespace App\Repositories\Transaction\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class TransactionOriginCriteria implements CriteriaInterface
{
    /**
     * @var
     */
    private $origin;

    /**
     * TransactionTypeCriteria constructor.
     * @param $origin
     */
    public function __construct($origin)
    {
        $this->origin = $origin;
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
        $origin = $this->origin;

        if ($origin) {

            return $model->whereHas('order', function($query) use ($origin){
                return $query->where('origin', $origin);
            });

        } else if (is_null($origin)) {

            return $model->whereHas('order', function($query){
                return $query->whereNull('origin');
            });

        }

        return $model;
    }
}
