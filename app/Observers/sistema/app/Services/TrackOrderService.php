<?php

namespace App\Services;

use Carbon\Carbon;
use App\Entities\Order;
use App\Services\Erp\Contracts\ErpServiceContract;
use App\Services\Tracker\TrackerContract;
use App\Services\Tracker\TrackerDetails;
use App\Services\Tracker\TrackerHistory;

class TrackOrderService
{
    /**
     * @var TrackerContract
     */
    private $tracker;
    /**
     * @var ErpServiceContract
     */
    private $erp;

    /**
     * TrackOrderService constructor.
     * @param TrackerContract $tracker
     * @param ErpServiceContract $erp
     */
    public function __construct(TrackerContract $tracker, ErpServiceContract $erp)
    {
        $this->tracker = $tracker;
        $this->erp = $erp;
    }

    /**
     * @param Order $order
     * @return TrackerDetails
     */
    public function findByOrder(Order $order)
    {

        if(!$order->invoice_id ){
            return $this->errorResponse("Pedido Recente, não deu entrada ainda no sistema", $order->created_at);
        }

        $nfe = $this->erp->getNfeNumberByInvoice( $order->invoice_id );

        if (!$nfe) {
            return $this->errorResponse("Nota Fiscal ainda não foi gerada", $order->updated_at);
        }

        return $this->tracker->findByNfe( $nfe );
    }

    private function errorResponse($history, Carbon $date)
    {
        return (new TrackerDetails)->addHistory(
            new TrackerHistory($history, null, $date)
        );
    }
}
