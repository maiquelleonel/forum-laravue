<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 1/13/16
 * Time: 11:41 AM
 */

namespace App\Repositories\Customers\Criteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class WithoutDocumentNumberCriteria implements CriteriaInterface
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
            return $query->whereNull("document_number")
                         ->orWhere("document_number", "");
        });
    }
}