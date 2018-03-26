<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class ExternalServiceSettings extends Model
{
    protected $table = 'external_service_settings';

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

    public function sites(){
        return $this->belongsToMany(Site::class,'external_service_settings_site');
    }
}
