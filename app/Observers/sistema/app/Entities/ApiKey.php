<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $table = 'api_key';

    protected $fillable = [
        "access_token",
        "user_id",
        "site_id"
    ];

    public function site()
    {
        return $this->belongsTo(Site::class, "site_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function generateKey()
    {
        $key = strtoupper(md5(microtime()));
        $key = substr_replace($key, "-", 24, 0);
        $key = substr_replace($key, "-", 16, 0);
        $key = substr_replace($key, "-", 8, 0);
        return $key;
    }
}