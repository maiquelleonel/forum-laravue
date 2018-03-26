<?php

namespace App\Entities;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property Collection rules
 */
class ConfigCommissionGroup extends Model
{
    use SoftDeletes;

    protected $table = "config_commission_group";

    protected $fillable = [
        "name"
    ];

    public function rules()
    {
        return $this->hasMany(ConfigCommissionRule::class, "config_commission_group_id");
    }

    public function users()
    {
        return $this->hasMany(User::class, "config_commission_group_id");
    }
}