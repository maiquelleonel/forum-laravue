<?php

namespace App\Repositories\Transaction\Criteria;

use Carbon\Carbon;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class TransactionCounterCriteria implements CriteriaInterface
{
    /**
     * @var Carbon|null
     */
    private $from;
    /**
     * @var Carbon|null
     */
    private $to;

    /**
     * OrderCounterCriteria constructor.
     * @param Carbon|null $from
     * @param Carbon|null $to
     */
    public function __construct(Carbon $from = null, Carbon $to = null)
    {
        $this->from = $from;
        $this->to = $to;
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
        $model = $model->selectRaw(
            "DATE_FORMAT(created_at,'%d/%m/%Y') as created,".
            "count(order_id ) as qty,".
            "'0' as amount"
        )->groupBy(\DB::raw("DATE_FORMAT(created_at,'%d/%m/%Y')"));

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