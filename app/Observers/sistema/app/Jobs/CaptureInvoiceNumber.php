<?php

namespace App\Jobs;

use App\Domain\OrderStatus;
use App\Entities\ErpSetting;
use App\Entities\Order;
use App\Services\Erp\Contracts\ErpServiceContract;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CaptureInvoiceNumber extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * @var Order
     */
    private $order;
    /**
     * @var ErpSetting
     */
    private $erpSetting;

    /**
     * Create a new job instance.
     *
     * @param Order $order
     * @param ErpSetting $erpSetting
     */
    public function __construct(Order $order, ErpSetting $erpSetting)
    {
        $this->order = $order;
        $this->erpSetting = $erpSetting;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = $this->order;

        if ($order->status == OrderStatus::INTEGRATED && $order->invoice_id) {

            /**
             * @var $erp ErpServiceContract
             */
            $erp = app("\\App\\Services\\Erp\\".$this->erpSetting->service, [$this->erpSetting]);

            if( $invoice = $erp->getNfeNumberByInvoice( $order->invoice_id ) ) {

                $order->update([
                    'invoice_number'    => $invoice
                ]);

            }
        }
    }
}
