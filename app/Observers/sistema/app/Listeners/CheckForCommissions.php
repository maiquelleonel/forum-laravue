<?php

namespace App\Listeners;

use App\Domain\NotificationType;
use App\Entities\OrderNotification;
use App\Events\OrderApproved;
use App\Events\OrderRefund;
use App\Services\Commissions\AssignCommission;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckForCommissions implements ShouldQueue
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
     * @param OrderApproved $orderApproved
     */
    public function handle(OrderApproved $orderApproved)
    {
        $this->assignCommission->assign($orderApproved->order);
    }
}
