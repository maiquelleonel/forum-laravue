<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class BundleGroup extends Model
{
    protected $table = "bundle_group";

    protected $fillable = [
        'name',
        'description',
        'image'
    ];

    public function bundles()
    {
        return $this->hasMany(Bundle::class);
    }

    public function upsells()
    {
        return $this->hasManyThrough(Upsell::class, Bundle::class, "bundle_group_id", "from_bundle_id");
    }

    public function additional()
    {
        return $this->hasManyThrough(Additional::class, Bundle::class, "bundle_group_id", "from_bundle_id");
    }

    public function sites()
    {
        return $this->hasMany(Site::class, "bundle_group_id");
    }

}