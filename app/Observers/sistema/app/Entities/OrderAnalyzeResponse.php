<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;
use App\Presenters\SitePresenter;

class OrderAnalyzeResponse extends Model
{
    protected $table = "order_analyze_response";

    protected $fillable = [
        "order_id",
        "rule_name",
        "rule_response",
        "batch",
        "status"
    ];

    public function getStatusAttribute($status)
    {
        return (bool) $status;
    }
}
