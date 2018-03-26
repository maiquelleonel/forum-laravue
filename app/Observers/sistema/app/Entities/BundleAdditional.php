<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;
use App\Presenters\BundleAdditionalPresenter;

class BundleAdditional extends Model
{
    use PresentableTrait;

    protected $presenter = BundleAdditionalPresenter::class;

    protected $table     = 'bundle_additional';

    protected $fillable  = [
        'bundle_id',
        'product_id',
        'price',
        'image',
        'order'
    ];

    public function product()
    {
        return $this->belongsTo( Product::class );
    }

    public function bundle()
    {
        return $this->belongsTo( Bundle::class );
    }
}
