<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;
use App\Presenters\BundlePresenter;

class Bundle extends Model
{
    use PresentableTrait;

    protected $table = "bundles";

    protected $presenter = BundlePresenter::class;

    protected $fillable = [
        'bundle_group_id',
        'image',
        'name',
        'description',
        'freight_value',
        'category',
        'installments',
        'retry_discount_1',
        'retry_discount_2'
    ];

    protected $appends = [
        'price',
        'old_price'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'bundle_product')
                    ->withPivot('product_qty', 'product_price');
    }

    public function upsell()
    {
        return $this->belongsToMany(Bundle::class, 'upsells', 'from_bundle_id', 'to_bundle_id');
    }

    public function site()
    {
        return $this->hasManyThrough(Site::class, BundleGroup::class);
    }

    public function sites()
    {
        return $this->hasMany(Site::class, "bundle_group_id", "bundle_group_id");
    }

    public function group()
    {
        return $this->belongsTo(BundleGroup::class, "bundle_group_id");
    }

    public function getPriceAttribute()
    {
        $total = 0;

        foreach ($this->products as $product) {
            $total+= $product->pivot->product_qty * $product->pivot->product_price;
        }

        return $total;
    }

    public function getOldPriceAttribute()
    {
        $total = 0;

        foreach ($this->products as $product) {
            $total+= $product->pivot->product_qty * $product->price;
        }

        return $total;
    }

    public function scopeOnlyDefault($query)
    {
        return $query->where("category", "default")->has("products");
    }

    public function scopeOnlyUpsell($query)
    {
        return $query->where("category", "upsell")->has("products");
    }

    public function scopeOnlyPromotional($query)
    {
        return $query->where("category", "promotional")->has("products");
    }

    public function scopeOnlyRemarketing($query)
    {
        return $query->where("category", "remarketing")->has("products");
    }

    public function additionalProducts()
    {
        return $this->hasMany(Additional::class, "from_bundle_id");
    }

}