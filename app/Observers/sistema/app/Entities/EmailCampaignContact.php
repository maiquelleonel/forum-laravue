<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class EmailCampaignContact extends Model
{
    protected $table = "email_campaign_contact";

    protected $fillable = [
        "customer_id",
        "lead_id",
        "list_id",
        "list_name"
    ];
}
