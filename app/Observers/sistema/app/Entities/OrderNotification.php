<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class OrderNotification extends Model
{
    protected $table = "order_notification";

    protected $fillable = [
        "order_id",
        "type"
    ];
}