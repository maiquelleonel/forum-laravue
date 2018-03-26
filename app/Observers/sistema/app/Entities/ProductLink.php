<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductLink extends Model
{
    protected $table = 'product_link';

    protected $fillable = [
        'product_id',
        'name',
        'url',
        'is_public'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}