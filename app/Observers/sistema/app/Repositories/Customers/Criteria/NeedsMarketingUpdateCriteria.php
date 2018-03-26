<?php

namespace App\Repositories\Customers\Criteria;


use App\Domain\LeadSegment;
use App\Domain\OrderStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PayPal\Api\Order;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class NeedsMarketingUpdateCriteria implements CriteriaInterface
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
                ->whereHas('orders', function ($query) {
                    return $query->whereIn("status", [
                        OrderStatus::APPROVED,
                        OrderStatus::AUTHORIZED,
                        OrderStatus::INTEGRATED,
                        OrderStatus::PENDING_INTEGRATION,
                        OrderStatus::PENDING_INTEGRATION_IN_ANALYZE
                    ]);
                })
                ->whereHas('emailCampaignContact', function($query) {
                    return $query->whereIn("list_name", [
                        LeadSegment::INTERESSADO,
                        LeadSegment::PAGAMENTO_NAO_APROVADO,
                        LeadSegment::PAGAMENTO_PENDENTE
                    ]);
                })
                ->where('created_at', '>=', Carbon::now()->subDays( 30 ));
    }
}