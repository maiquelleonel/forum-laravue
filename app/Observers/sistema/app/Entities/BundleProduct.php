<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class BundleProduct extends Model
{
    protected $table = "bundle_product";

    protected $fillable = [
        'bundle_id',
        'product_id',
        'product_qty',
        'product_price'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function bundles()
    {
        return $this->hasMany(Bundle::class, 'bundle_product');
    }
}