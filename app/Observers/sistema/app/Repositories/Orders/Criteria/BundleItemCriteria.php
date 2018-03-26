<?php

namespace App\Repositories\Orders\Criteria;

use App\Order;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class BundleItemCriteria implements CriteriaInterface
{

    /**
     * @var string bundle
     */
    private $bundle;

    public function __construct($bundle)
    {
        $this->bundle = $bundle;
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
        if($this->bundle) {
            $bundle = $this->bundle;
            return $model->whereHas('itemsBundle', function($item) use ($bundle){
                return $item->where('bundle_id', $bundle);
            });
        }
        return $model;
    }
}