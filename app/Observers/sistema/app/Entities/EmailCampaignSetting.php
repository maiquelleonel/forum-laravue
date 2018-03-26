<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class EmailCampaignSetting extends Model
{
    protected $table = "email_campaign_setting";

    protected $fillable = [
        "name",
        "service",
        "auth_type",
        "username",
        "password",
        "api_key",
        "oauth_secret_key",
        "oauth_client_key",
        "base_url"
    ];

    public function sites()
    {
        return $this->hasMany(Site::class);
    }
}