<?php

namespace App\Repositories\Customers\Criteria;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class CustomerCounterCriteria implements CriteriaInterface
{

    /**
     * @var Carbon
     */
    private $from;

    /**
     * @var Carbon
     */
    private $to;

    public function __construct(Carbon $from = null, Carbon $to = null)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Apply criteria in query repository
     *
     * @param Model $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $model = $model->selectRaw("DATE_FORMAT(created_at,'%d/%m/%Y') as created, count(*) as qty")
                       ->groupBy(\DB::raw("DATE_FORMAT(created_at,'%d/%m/%Y')"));

        if($this->from && $this->to) {
            return $model->whereBetween('created_at', [$this->from, $this->to]);
        }

        if($this->from) {
            return $model->where('created_at', '>=',$this->from);
        }

        if($this->to) {
            return $model->where('created_at', '<=',$this->to);
        }

        return $model;
    }
}