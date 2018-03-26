<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;
use App\Presenters\UpsellPresenter;

class Upsell extends Model
{
    use PresentableTrait;

    protected $table = 'upsells';

    protected $presenter = UpsellPresenter::class;

    protected $fillable = [
        'from_bundle_id',
        'to_bundle_id'
    ];
    
    public function fromBundle() {
        return $this->belongsTo(Bundle::class, 'from_bundle_id');
    }

    public function toBundle() {
        return $this->belongsTo(Bundle::class, 'to_bundle_id');
    }
}
