<?php

namespace App\Repositories\Orders\Criteria;

use App\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class PaymentDateCriteria implements CriteriaInterface
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
     * @var string
     */
    private $dateField = 'created_at';
    /**
     * @var bool
     */
    private $byPaymentDay;

    /**
     * OrderCounterCriteria constructor.
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @param bool $byPaymentDay
     */
    public function __construct(Carbon $from = null, Carbon $to = null, $byPaymentDay = false)
    {
        $this->from = $from;
        $this->to = $to;
        $this->byPaymentDay = $byPaymentDay;
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
        if ($this->byPaymentDay) {
            $this->dateField = 'paid_at';
            $model->orderBy('paid_at', 'asc');
        } else {
            $model->orderBy('created_at', 'asc');
        }

        if($this->from && $this->to) {
            return $model->whereBetween($this->dateField, [$this->from, $this->to]);
        }

        if($this->from) {
            return $model->where($this->dateField, '>=',$this->from);
        }

        if($this->to) {
            return $model->where($this->dateField, '<=',$this->to);
        }

        return $model;
    }
}