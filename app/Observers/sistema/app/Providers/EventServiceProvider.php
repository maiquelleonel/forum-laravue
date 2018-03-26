<?php

namespace App\Providers;

use App\Events\OrderApproved;
use App\Events\OrderAuthorized;
use App\Events\OrderBilletCreated;
use App\Events\OrderPaid;
use App\Events\OrderPendingIntegration;
use App\Events\OrderRefund;
use App\Events\OrderUpSold;
use App\Listeners\CheckForCommissions;
use App\Listeners\RecheckCommissions;
use App\Listeners\SendOrderAuthorizedMail;
use App\Listeners\SendOrderBilletMail;
use App\Listeners\SendOrderPaidMail;
use App\Listeners\SendOrderPaidSms;
use App\Listeners\SendOrderRefundMail;
use App\Listeners\SendPostBackNotification;
use App\Listeners\SendUserAccessMail;
use App\Listeners\SendOrderBilletPhoneToEvolux;
use App\Listeners\IntegrationErrorsSlackNotifications;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        OrderPaid::class => [
            SendOrderPaidMail::class,
            SendOrderPaidSms::class
        ],

        OrderRefund::class => [
            SendOrderRefundMail::class
        ],

        OrderAuthorized::class => [
            SendOrderAuthorizedMail::class
        ],

        OrderApproved::class => [
            CheckForCommissions::class
        ],

        OrderUpSold::class => [
            RecheckCommissions::class
        ],

        'eloquent.saved: App\Entities\User' => [
            SendUserAccessMail::class
        ],

        'eloquent.created: App\Entities\SalesCommission' => [
            SendPostBackNotification::class
        ],

        OrderBilletCreated::class => [
            SendOrderBilletMail::class,
            SendOrderBilletPhoneToEvolux::class,
        ],

        OrderPendingIntegration::class => [
            IntegrationErrorsSlackNotifications::class
        ]
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        //
    }
}
