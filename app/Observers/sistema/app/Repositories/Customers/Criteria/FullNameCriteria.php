<?php

namespace App\Repositories\Customers\Criteria;

use App\Entities\Customer;
use App\Entities\Order;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class FullNameCriteria implements CriteriaInterface
{


    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
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
        if ($this->request->has('search')) {
            if ($model instanceof Customer) {
                return $this->applyFullNameSearch($model, $repository, $this->request->get("search"));
            } else if ($model instanceof Order) {
                return $model->whereHas("customer", function($model) use ($repository){
                    return $this->applyFullNameSearch($model, $repository, $this->request->get("search"));
                });
            }
        }
        return $model;
    }


    /**
     * Apply search by specify input value
     * @param $model
     * @param $repository
     * @param $inputValue
     * @return mixed
     */
    protected function applyFullNameSearch($model, $repository, $inputValue)
    {
        return $model->where(function ($model) use ($repository, $inputValue) {
            foreach ($this->getFieldsSearchable() as $field) {
                $model = $model->orWhere(function($queryBuilder) use ($field, $inputValue){
                    array_map(function($value) use ($queryBuilder, $field){
                        $queryBuilder->where(\DB::raw($field), "LIKE", "%".$value."%");
                    }, explode(" ", $inputValue));
                });
            }
        });
    }


    /**
     * Get Fields to search
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return ['concat(firstname, " ", lastname)', 'email', 'document_number', 'REPLACE(document_number, "-", "")'];
    }
}