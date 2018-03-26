<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class AffiliatePixel extends Model
{
    protected $table = "affiliate_pixel";

    protected $fillable = [
        "name",
        "code",
        "user_id",
        "site_id",
        "page"
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}