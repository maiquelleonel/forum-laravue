<?php

namespace App\Listeners;

use App\Domain\NotificationType;
use App\Entities\OrderNotification;
use App\Events\OrderApproved;
use App\Events\OrderRefund;
use App\Events\OrderUpSold;
use App\Services\Commissions\AssignCommission;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecheckCommissions implements ShouldQueue
{
    /**
     * @var AssignCommission
     */
    private $assignCommission;

    /**
     * CheckForCommissions constructor.
     * @param AssignCommission $assignCommission
     */
    public function __construct(AssignCommission $assignCommission)
    {
        $this->assignCommission = $assignCommission;
    }

    /**
     * Handle the event.
     *
     * @param OrderUpSold $orderUpSold
     */
    public function handle(OrderUpSold $orderUpSold)
    {
        $this->assignCommission->update($orderUpSold->order);
    }
}
