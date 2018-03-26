<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;
use App\Presenters\CustomerPresenter;
use App\Services\Payment\Response\CreditCard;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property integer id
 * @property integer site_id
 * @property float conversion_rate
 * @property string code
 */
class Currency extends Model
{
    protected $table = "currencies";

    protected $fillable = [
        "name",
        "code",
        "prefix",
        "suffix",
        "decimals",
        "decimal",
        "thousand",
        "conversion_rate"
    ];
}
