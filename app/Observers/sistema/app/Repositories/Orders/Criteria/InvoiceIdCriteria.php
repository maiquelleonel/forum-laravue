<?php

namespace App\Repositories\Orders\Criteria;

use App\Order;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class InvoiceIdCriteria implements CriteriaInterface
{

    /**
     * @var status
     */
    private $ids;

    public function __construct($ids)
    {
        $this->ids = $ids;
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
        if($this->ids) {
            $ids = $this->ids;
            return $model->where(function($query) use ($ids) {
                $query->where('id', $ids)
                      ->orWhere('invoice_id', $ids);
            });
        }
        return $model;
    }
}