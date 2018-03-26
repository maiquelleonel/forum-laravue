<?php

namespace App\Repositories\Bundle;

use App\Entities\Bundle;
use App\Repositories\BaseEloquentRepository;

/**
 * Class BundleRepositoryEloquent
 * @package namespace App\Repositories;
 */
class BundleRepository extends BaseEloquentRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Bundle::class;
    }

    public function getMinorPrice()
    {
        return $this->model->query()->orderBy('price', 'asc')->first();
    }

    public function getBiggestPrice()
    {
        return $this->model->query()->orderBy('price', 'desc')->first();
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        $model = parent::create($attributes);

        $this->updateItems($model, isset($attributes["items"]) ? $attributes["items"] : []);

        return $model;
    }

    /**
     * @param array $attributes
     * @param $id
     * @return mixed
     */
    public function update(array $attributes, $id)
    {
        $model = parent::update($attributes, $id);

        $this->updateItems($model, isset($attributes["items"]) ? $attributes["items"] : []);

        return $model;
    }

    public function updateItems($model, array $items)
    {
        if (count($items) > 0) {
            $model->products()->detach();
            $model->products()->attach(
                $this->hydrateItems( $items )
            );
        }
    }

    /**
     * @param array $items
     * @return mixed
     */
    private function hydrateItems(array $items )
    {
        array_walk($items, function(&$item) {
            if (isset($item["product_price"])) {
                $item["product_price"] = trim( str_ireplace(["R$", ".", ","], ["", "", "."], $item["product_price"]) );
            } else {
                $item["product_price"] = 1;
            }
        });

        return $items;
    }
}
