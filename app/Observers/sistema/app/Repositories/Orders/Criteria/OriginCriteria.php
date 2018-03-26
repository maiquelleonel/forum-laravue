<?php

namespace App\Repositories\Orders\Criteria;

use App\Entities\SalesCommission;
use App\Order;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class OriginCriteria implements CriteriaInterface
{

    /**
     * @var string origin
     */
    private $origin;

    /**
     * @var string table
     */
    private $table;

    public function __construct($origin)
    {
        $this->origin = $origin == "site" ? null : $origin;
        $this->table  = "orders";
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
        if($this->origin){
            if($model->getModel() instanceof SalesCommission){
                $this->table = "origin_orders";
                $model = $model->join("orders as {$this->table}", "{$this->table}.id", "=", "sales_commission.order_id");
            }
        }
        return $this->applyCondition($model);
    }

    private function applyCondition($model)
    {
        if(is_array($this->origin)) {
            return $model->where(function($model){
                $model->whereIn("{$this->table}.origin", $this->origin);
                if( in_array(null, $this->origin ) ){
                    $model->orWhereNull("{$this->table}.origin");
                }
            });
        } else if($this->origin) {
            return $model->where("{$this->table}.origin", $this->origin);
        } else if(is_null($this->origin)){
            return $model->whereNull("{$this->table}.origin");
        }
        return $model;
    }
}