<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Additional extends Model
{
    protected $table = 'additional';

    protected $fillable = [
        'name',
        'from_bundle_id',
        'product_id',
        'order',
        'qty_max',
        'price'
    ];
    
    public function fromBundle() {
        return $this->belongsTo(Bundle::class, 'from_bundle_id');
    }

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
