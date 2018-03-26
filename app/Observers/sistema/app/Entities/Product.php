<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;
use App\Presenters\ProductPresenter;

/**
 * @SWG\Definition(
 *     required={
 *          "sku", "name", "qty", "value"
 *     },
 *     definition="ProductItem",
 *      @SWG\Property(property="sku", type="string", example="SKU_1"),
 *      @SWG\Property(property="name", type="string", example="LightSaber V2.0"),
 *      @SWG\Property(property="qty", type="integer", example="10"),
 *      @SWG\Property(property="value", type="integer", example="199.89")
 * )
 *
 * @SWG\Definition(
 *     definition="Product",
 *      @SWG\Property(property="sku", type="string", example="SKU_1"),
 *      @SWG\Property(property="price", type="number", example="199.89"),
 *      @SWG\Property(property="label", type="string", example="LightSaber V2.0"),
 *      @SWG\Property(property="name", type="string", example="LightSaber V2.0"),
 *      @SWG\Property(property="description", type="string", example="About LightSaber V2.0"),
 *      @SWG\Property(property="image", type="string", example="/uploads/darth-vader.png"),
 *      @SWG\Property(property="inventory", type="integer", example="400"),
 *      @SWG\Property(property="cost", type="number", example="9.99"),
 *      @SWG\Property(property="ipi", type="number", example="10.90"),
 *      @SWG\Property(property="is_app", type="boolean", example="false"),
 * )
 */
class Product extends Model
{
    use PresentableTrait;

    protected $presenter = ProductPresenter::class;

    protected $table = 'products';

    protected $fillable = [
        'sku',
        'label',
        'price',
        'name',
        'description',
        'image',
        'inventory',
        'cost',
        'ipi',
        'company_id',
        'is_app'
    ];

    public function links()
    {
        return $this->hasMany(ProductLink::class);
    }

    public function publicLinks()
    {
        return $this->hasMany(ProductLink::class)->where('is_public', true);
    }

    public function bundles()
    {
        return $this->belongsToMany(Bundle::class, "bundle_product")
                    ->groupBy("bundle_id");
    }

    public function company()
    {
        return $this->belongsTo(Company::class, "company_id");
    }
}