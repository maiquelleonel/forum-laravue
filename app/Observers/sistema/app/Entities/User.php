<?php

namespace App\Entities;

use Artesaos\Defender\Traits\HasDefender;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Presenters\UserPresenter;
use Laracasts\Presenter\PresentableTrait;

/**
 * @property ConfigCommissionGroup groupCommission
 * @property integer id
 */
class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    public $new_password = null;

    use Authenticatable, Authorizable, CanResetPassword, SoftDeletes, PresentableTrait, HasDefender;

    protected $presenter = UserPresenter::class;



    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'affiliate_id',
        'config_commission_group_id',
        'deleted_at',
        'locale',
        'currency_id',
        'evolux_login',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function commissions()
    {
        return $this->hasMany(SalesCommission::class, "user_id");
    }

    public function groupCommission()
    {
        return $this->belongsTo(ConfigCommissionGroup::class, "config_commission_group_id");
    }

    public function commissionRules()
    {
        return $this->hasMany(ConfigCommissionRule::class, "config_commission_group_id", "config_commission_group_id");
    }

    public function isAffiliate()
    {
        return !empty($this->affiliate_id) and !is_null($this->affiliate_id);
    }

    public function getOfferSitesAttribute()
    {
        if (!array_key_exists('offerSites', $this->relations)) {
            $this->offerSites();
        }

        return $this->getRelation('offerSites');
    }

    public function offerSites()
    {

        $fields = ['site.*', 'config_commission_group_id', \DB::raw("{$this->id} as user_id")];

        if ($this->affiliate_id) {
            $fields[] = \DB::raw("{$this->affiliate_id} as affiliate_id");
        }

        $sites = Site::select($fields)
                        ->join('config_commission_rule_site', 'config_commission_rule_site.site_id', '=', 'site.id')
                        ->join(
                            'config_commission_rule',
                            'config_commission_rule_site.config_commission_rule_id',
                            '=',
                            'config_commission_rule.id'
                        )
                        ->where(
                            'config_commission_rule.config_commission_group_id',
                            $this->config_commission_group_id
                        )
                        ->groupBy('config_commission_rule_site.site_id')
                        ->get();

        $hasMany = new HasMany(
            User::query(),
            $this,
            'config_commission_rule.config_commission_group_id',
            'config_commission_group_id'
        );

        $hasMany->matchMany(array($this), $sites, 'offerSites');

        $this->setRelation('offerSites', $sites);

        return $this;
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, "currency_id");
    }

    public function pixels()
    {
        return $this->hasMany(AffiliatePixel::class, "user_id");
    }
}
