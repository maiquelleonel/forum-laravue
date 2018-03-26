<?php

namespace App\Repositories\Orders\Criteria;

use App\Order;
use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class WithoutInvoiceCriteria implements CriteriaInterface
{

    /**
     * Apply criteria in query repository
     *
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        return $model->where(function($query){
            $query->whereNull("invoice_number")
                  ->orWhere("invoice_number", "");
        });
    }
}