<?php

namespace App\Repositories\Additional;

use App\Entities\Additional;
use App\Entities\Order;
use App\Repositories\BaseEloquentRepository;

class AdditionalRepository extends BaseEloquentRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Additional::class;
    }

    /**
     * @param Order $order
     * @param int $minOrder
     * @return bool|Additional
     */
    public function getNextAdditional(Order $order, $minOrder = 0 )
    {
        if ( !session()->has('last_additional') ) {
            $last = session()->put(['last_additional', null]);
        } else {
            $last = $this->find( session()->get('last_additional') );
        }

        if ($order->bundles) {

            $bundle = $order->bundles->first();

            $additional = null;

            if ($last) {
                $additional = $bundle->additionalProducts()
                    ->where('order', '>', $last->order)
                    ->where('order', '>', $minOrder)
                    ->first();
            } else {
                $additional = $bundle->additionalProducts()
                    ->orderBy('order', 'asc')
                    ->where('order', '>', $minOrder)
                    ->first();
            }

            if( $additional ) {

                $contains = $order->products->contains(function($key, $product) use ($additional){
                    return $product->id == $additional->product_id;
                });

                if (!$contains) {
                    return $additional;
                }
            }
        }

        return false;
    }
}