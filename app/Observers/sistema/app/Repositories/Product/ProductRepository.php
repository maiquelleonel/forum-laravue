<?php

namespace App\Repositories\Product;

use Prettus\Repository\Eloquent\BaseRepository;
use App\Entities\Product;

/**
 * Class ProductRepositoryEloquent
 * @package namespace App\Repositories;
 */
class ProductRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Product::class;
    }

}
