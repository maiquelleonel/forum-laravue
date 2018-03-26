<?php

namespace App\Observers;

use App\Domain\OrderStatus;
use App\Domain\BundleCategory;
use App\Events\OrderApproved;
use App\Events\OrderAuthorized;
use App\Events\OrderPaid;
use App\Events\OrderPendingIntegration;
use App\Events\OrderRefund;
use App\Entities\Order;
use App\Jobs\SendCustomerWithCanceledOrderToEvolux;
use App\Jobs\SendUpsellNotificationOnSlack;
use App\Jobs\SendCustomerWithoutUpsellToEvolux;
use App\Services\Tracking\VisitPage;
use App\Support\SiteSettings;
use Carbon\Carbon;

class OrderObserver
{
    public function __construct(SiteSettings $siteSettings)
    {
        $this->siteSettings = $siteSettings;
    }

    public function creating(Order $order)
    {
        $now         = Carbon::now();
        $timestamp   = $now->timestamp;
        $_id         = strrev($now->format('dmYHis'));
        $microtime   = preg_replace('/\\D/', '', microtime());
        $order->hash = "$_id-$timestamp-$microtime";
    }

    public function saving(Order $order)
    {
        if (is_null($order->page_visit_id) && config("tracking.enabled")) {
            if ($order->origin == "system" or $order->origin == "API") {
                if ($visit = $order->customer->firstVisit) {
                    $order->page_visit_id = $visit->id;
                }
            } else {
                /**
                 * @var $visitPage VisitPage
                 */
                $visitPage = app(VisitPage::class);
                if ($visit = $visitPage->getCurrentVisit()) {
                    $order->page_visit_id = $visit->id;
                }
            }
        }


        if ($order->status == OrderStatus::APPROVED && !$order->paid_at) {
            $order->paid_at = (new Carbon)->now();
            event((new OrderPaid($order))->delay(300));
        }

        if ($order->status == OrderStatus::REFUND) {
            event(new OrderRefund($order));
        }

        if ($order->status == OrderStatus::AUTHORIZED) {
            event(new OrderAuthorized($order));
        }

        if (!auth()->user() && $order->isDirty("status") && $order->status == OrderStatus::PENDING_INTEGRATION) {
            event(new OrderPendingIntegration($order));
        }
    }

    public function updating(Order $order)
    {
        if (!auth()->user() && $order->isDirty('status')) {
            if ($order->origin != 'system' or is_null($order->origin)) {
                if ('creditcard' == strtolower($order->payment_type_collection)) {
                    $this->dispatchJobByOrderStatus($order);
                }
                /* elseif ( 'boleto' == strtolower($order->payment_type_collection) ){
                    Code in App\Listeners\SendOrderBilletPhoneToEvolux
                }*/
            }
        }
    }


    private function dispatchJobByOrderStatus($order)
    {
        $job = false;
        switch ($order->status) {
            case OrderStatus::APPROVED:
                if ($order->userCanUpsell()) {
                    $job = new SendCustomerWithoutUpsellToEvolux($order);
                }
                break;
            case OrderStatus::CANCELED:
                $job = new SendCustomerWithCanceledOrderToEvolux($order->customer);
                break;
        }

        if ($job !== false) {
            $job->delay(300); //5 min
            dispatch($job);
        }
    }

    public function updated(Order $order)
    {
        if ($order->isDirty("status")) {
            if (in_array($order->status, OrderStatus::approved())) {
                event(new OrderApproved($order));
            }

            if ($order->isPaid() && $order->upsellOrder &&
                $order->upsellOrder->status == OrderStatus::AUTHORIZED) {
                $job = new SendUpsellNotificationOnSlack($order->upsellOrder);
                $job->delay(120);// 2 min
                dispatch($job);
            }
        }
    }
}
