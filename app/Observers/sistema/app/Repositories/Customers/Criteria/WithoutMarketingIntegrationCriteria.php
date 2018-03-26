<?php

namespace App\Repositories\Customers\Criteria;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class WithoutMarketingIntegrationCriteria implements CriteriaInterface
{
    /**
     * Apply criteria in query repository
     *
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        /**
         * @var $model Model
         */
        return $model
                ->query()
                ->where('created_at', '<=', Carbon::now()->subMinutes( 40 ))
                ->where('email', '!=', '')
                ->has('site.emailCampaignSetting')
                ->doesntHave('emailCampaignContact');
    }
}