<?php
/**
 * Created by PhpStorm.
 * User: dlima
 * Date: 10/11/17
 * Time: 14:57
 */

namespace App\Entities;

use App\Presenters\CampaignCostPresenter;
use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;

class CampaignCost extends Model
{
    use PresentableTrait;

    protected $presenter = CampaignCostPresenter::class;

    protected $table = "campaign_costs";

    protected $fillable = [
        "utm_campaign",
        "cost",
        "cost_day"
    ];
}