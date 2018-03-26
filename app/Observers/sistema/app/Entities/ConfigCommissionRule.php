<?php

namespace App\Entities;


use App\Presenters\CommissionRulePresenter;
use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;

/**
 * @property integer id
 * @property integer config_commission_group_id
 * @property string type
 * @property float value
 * @property float shaving_rate
 * @property integer currency_id
 * @property Currency currency
 */
class ConfigCommissionRule extends Model
{
    use PresentableTrait;

    protected $presenter = CommissionRulePresenter::class;

    const TYPE_PERCENTAGE   = "PERCENTAGE";
    const TYPE_FIXED        = "FIXED";

    protected $table = "config_commission_rule";

    protected $fillable = [
        "config_commission_group_id",
        "type",
        "value",
        "shaving_rate",
        "currency_id"
    ];

    public function group()
    {
        return $this->belongsTo(ConfigCommissionGroup::class, "config_commission_group_id");
    }

    public function sites()
    {
        return $this->belongsToMany(Site::class, "config_commission_rule_site", "config_commission_rule_id", "site_id");
    }

    public function users()
    {
        return $this->hasMany(User::class, "config_commission_group_id", "config_commission_group_id");
    }

    public function origins()
    {
        return $this->belongsToMany(
            ConfigCommissionRuleOrigin::class,
            "config_commission_rule_to_origin",
            "config_commission_rule_id",
            "config_commission_rule_origin_id"
        );
    }

    public function paymentTypes()
    {
        return $this->belongsToMany(
            ConfigCommissionRulePaymentType::class,
            "config_commission_rule_to_payment_type",
            "config_commission_rule_id",
            "config_commission_rule_payment_type_id"
        );
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, "currency_id");
    }
}