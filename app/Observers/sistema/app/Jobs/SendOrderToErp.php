<?php

namespace App\Jobs;

use App\Domain\OrderStatus;
use App\Entities\ErpSetting;
use App\Entities\Order;
use App\Services\Erp\Contracts\ErpServiceContract;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderToErp extends Job implements ShouldQueue
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

        if (in_array($order->status, [OrderStatus::APPROVED, OrderStatus::PENDING_INTEGRATION, OrderStatus::PENDING_INTEGRATION_IN_ANALYZE]) && $order->status != OrderStatus::INTEGRATED) {

            /**
             * @var $erp ErpServiceContract
             */
            $erp = app("\\App\\Services\\Erp\\".$this->erpSetting->service, [$this->erpSetting]);

            $this->sendOrder($order, $erp);
        }
    }

    /**
     * @param Order $order
     * @param ErpServiceContract $erp
     * @return mixed
     */
    private function sendOrder($order, $erp)
    {
        try {
            if ($this->erpSetting->run_validations) {
                $response = $erp->validate($order);

                $passValidations = $response->where("status", false)->count() === 0;

                // Validation fails
                if( !$passValidations ) {
                    $order->update(['status' => OrderStatus::PENDING_INTEGRATION]);
                    throw new \Exception("Pedido com falhas " . $order->id);
                }
            }

            $this->makeRequest($order, $this->erpSetting->generate_invoice, $erp);
        } catch (\Exception $e) {
            // Pedido com Falha de Validação
        }
    }

    /**
     * @param Order $order
     * @param boolean $generateInvoice
     * @param ErpServiceContract $erp
     */
    private function makeRequest($order, $generateInvoice, $erp)
    {
        $response = $erp->sendOrder($order, $generateInvoice);
        if($response->getInvoiceId()){
            $data = [
                'invoice_id'        => $response->getInvoiceId(),
                'invoice_number'    => $response->getInvoiceNumber(),
                'tracking'          => $response->getTrackCode(),
                'status'            => OrderStatus::INTEGRATED
            ];
            $order->update($data);
            foreach($order->approvedUpsellOrders as $upSoldOrder) {
                $upSoldOrder->update($data);
            }
        }
    }
}
