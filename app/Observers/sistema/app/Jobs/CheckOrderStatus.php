<?php

namespace App\Jobs;

use App\Domain\OrderStatus;
use App\Entities\Order;
use App\Services\Gateways\MundiPagg;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckOrderStatus extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $order;

    /**
     * Create a new job instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $paymentCollection = $this->order->payment_type_collection;
        $paymentType       = $this->order->payment_type;

        if ($paymentCollection == "CreditCard" && $paymentType == "MundiPagg") {
            $this->verifyMundiPagg($this->order);
        }
    }

    /**
     * @param Order $order
     */
    private function verifyMundiPagg($order)
    {
        $transaction = $order->lastValidCreditCardPayment();

        if ($transaction && $transaction->getTransactionToken() && $transaction->getStoreToken()) {

            $siteSettings = new SiteSettings(new Request);
            $siteSettings->init($order->customer->site);
            /**
             * @var MundiPagg $mundi
             */
            $mundi = new MundiPagg($siteSettings->getPaymentSettings());
            $response = $mundi->makeConsult(
                $transaction->getTransactionToken(),
                $transaction->getStoreToken()
            );

            switch ($response->getTransactionStatus()) {
                case "Captured":
                    $this->approveOrder($order);
                    break;

                case "Refunded":
                case "Canceled":
                case "Voided":
                    $this->refundOrder($order);
                    break;
            }
        }
    }

    private function approveOrder(Order $order)
    {
        $order->status = OrderStatus::APPROVED;
        $order->save();
    }

    private function refundOrder(Order $order)
    {
        $order->status = OrderStatus::REFUND;
        $order->save();
    }
}
