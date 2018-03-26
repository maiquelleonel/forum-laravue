<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class PageVisitUrl extends Model
{
    protected $table = 'page_visit_url';

    protected $fillable = [
        "full_url",
        "prefix",
        "domain",
        "path",
        "query",
        "page_visit_id"
    ];

}