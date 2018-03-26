<?php

namespace App\Repositories\SalesCommission\Criteria;

use App\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class CommissionCounterCriteria implements CriteriaInterface
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
    private $dateField = 'sales_commission.created_at';

    /**
     * @var bool
     */
    private $byPaymentDay;

    /**
     * @var string
     */
    private $groupByField;

    /**
     * OrderCounterCriteria constructor.
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @param bool $byPaymentDay
     * @param string $groupByField
     */
    public function __construct(Carbon $from = null, Carbon $to = null, $byPaymentDay = false, $groupByField = "sales_commission.created_at")
    {
        $this->from = $from;
        $this->to = $to;
        $this->byPaymentDay = $byPaymentDay;
        $this->groupByField = $groupByField;
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
            $this->dateField = 'sales_commission.paid_at';
        }

        if($this->groupByField){

            $field = $this->groupByField;

            if(is_bool($this->groupByField) OR in_array($this->groupByField,["sales_commission.paid_at", "sales_commission.created_at"])) {
                $this->groupByField = "DATE_FORMAT({$this->dateField},'%d/%m/%Y')";
                $field = "DATE_FORMAT({$this->dateField},'%d/%m/%Y') as created";
            }

            $model = $model->selectRaw(
                "{$field},".
                "count(*) as qty,".
                "sum(value) as amount"
            )->groupBy(\DB::raw($this->groupByField));
        } else {
            $model = $model->selectRaw(
                "1 as created,".
                "count(*) as qty,".
                "sum(value) as amount"
            );
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