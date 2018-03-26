<?php
namespace App\Repositories\OrderItems;

use App\Repositories\BaseEloquentRepository;
use App\Entities\OrderItemBundle;

class OrderItemBundleRepository extends BaseEloquentRepository
{

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return OrderItemBundle::class;
    }

    /**
     * @inheritdoc
     */
    public function create(array $attributes)
    {
        if(isset($attributes['price']) && !is_numeric($attributes['price'])) {
            $attributes['price'] = str_ireplace(['R$', '.', ',', ' '], ['', '', '.', ''], $attributes['price']);
        }

        if(!isset($attributes['qty'])) {
            $attributes['qty'] = 1;
        }

        return parent::create($attributes);
    }
}