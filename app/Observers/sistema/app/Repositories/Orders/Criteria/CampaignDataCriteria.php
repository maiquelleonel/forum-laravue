<?php

namespace App\Repositories\Orders\Criteria;

use App\Entities\Customer;
use App\Entities\Order;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class CampaignDataCriteria implements CriteriaInterface
{
    private $fields;

    protected $allowedFields = [
        "utm_source",
        "utm_campaign",
        "utm_content",
        "utm_term",
        "custom_var_v1",
        "custom_var_v2",
        "custom_var_v3",
        "custom_var_v4",
        "custom_var_v5"
    ];

    /**
     * OrderCounterCriteria constructor.
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
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
        if ($model->getModel() instanceof Customer) {

            // @todo apply criteria to Customer

        } else if ($model->getModel() instanceof Order) {

            return $model->whereHas("visit", function($model){
                return $this->applySearch($model);
            });

        }

        return $model;
    }

    private function applySearch($model)
    {
        foreach($this->fields as $field=>$value){
            if (in_array($field, $this->allowedFields)) {
                if (is_null($value)) {
                    $model->whereNull($field);
                } else if (strlen($value)>0) {
                    $model->where($field, $value);
                }
            }
        }

        return $model;
    }
}