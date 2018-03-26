<?php

namespace App\Repositories\Customers\Criteria;

use App\Entities\Customer;
use App\Entities\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class CustomerDataCriteria implements CriteriaInterface
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
     * @param $model Builder
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        if ($model->getModel() instanceof Customer) {

            return $this->applySearch($model);

        } else if ($model->getModel() instanceof Order) {

            return $model->whereHas("customer", function($model){
                return $this->applySearch($model);
            });

        }

        return $model;
    }

    private function applySearch($model)
    {
        $searchable = $this->getFieldsSearchable();
        $fields     = $this->request->only(array_keys($this->getFieldsSearchable()));

        foreach($fields as $fieldName=>$value) {

            if ($value) {
                $model = $model->where(
                    \DB::raw($searchable[$fieldName]), "LIKE", "%". str_ireplace(" ", "%", $value) ."%"
                );
            }
        }

        return $model;
    }

    /**
     * Get Fields to search
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return [
            "name"                      => 'concat(firstname, " ", lastname)',
            "email"                     => 'email',
            "document_number"           => 'REPLACE(document_number, "-", "")',
            "telephone"                 => 'REPLACE(telephone, "-", "")',
            "postcode"                  => 'REPLACE(postcode, "-", "")',
            "address_street"            => 'address_street',
            "address_street_number"     => 'address_street_number',
            "address_street_complement" => 'address_street_complement',
            "address_street_district"   => 'address_street_district',
            "address_city"              => 'address_city',
        ];
    }
}