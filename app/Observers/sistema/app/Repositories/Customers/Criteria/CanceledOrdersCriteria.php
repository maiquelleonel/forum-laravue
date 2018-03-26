<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 1/13/16
 * Time: 11:41 AM
 */

namespace App\Repositories\Customers\Criteria;


use App\Domain\OrderStatus;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class CanceledOrdersCriteria implements CriteriaInterface
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
        $approvals = array_merge(
            array_values(OrderStatus::approved()),[
                OrderStatus::REFUND,
                OrderStatus::PENDING
            ]
        );

        return $model->whereDoesntHave("orders", function($query) use ($approvals){
            // Não tenha pedido aprovado, autorizado, integrado nem pendente
            $query->whereIn("status", $approvals)
            ->orWhere("origin", "system");
        })->has("orders");
    }
}