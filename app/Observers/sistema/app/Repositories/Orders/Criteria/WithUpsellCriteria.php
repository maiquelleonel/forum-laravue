<?php

namespace App\Repositories\Orders\Criteria;

use App\Domain\BundleCategory;
use App\Order;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class WithUpsellCriteria implements CriteriaInterface
{
    /**
     * @var null
     */
    private $bundleId;

    /**
     * WithUpsellCriteria constructor.
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

        return $model->whereHas('bundles', function($query) use ($bundleId){

            $query = $query->where('category', BundleCategory::UPSELL);

            if ($bundleId) {
                $query = $query->where('id', $bundleId);
            }

            return $query;
        });
    }
}