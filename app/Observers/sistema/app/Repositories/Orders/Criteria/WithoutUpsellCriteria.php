<?php

namespace App\Repositories\Orders\Criteria;

use App\Order;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class WithoutUpsellCriteria implements CriteriaInterface
{
    /**
     * @var null
     */
    private $bundleId;

    /**
     * WithoutUpsellCriteria constructor.
     * @param null $bundleId
     */
    public function __construct($bundleId = null)
    {
        $this->bundleId = $bundleId;
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
        $bundleId = $this->bundleId;

        return $model->whereHas('items', function($query) use ($bundleId){

            $query = $query->whereNotNull('bundle_id')
                           ->where('qty', 1);

            if ($bundleId) {
                $query = $query->where('bundle_id', $bundleId);
            }

            return $query;
        });
    }
}