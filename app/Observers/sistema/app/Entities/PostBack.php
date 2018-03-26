<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property string url
 * @property integer user_id
 * @property integer site_id
 * @property string method
 */
class PostBack extends Model
{
    protected $table = "postback";

    protected $fillable = [
        "url",
        "user_id",
        "site_id",
        "method"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}