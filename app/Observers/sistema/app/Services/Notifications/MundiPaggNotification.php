<?php

namespace App\Services\Notifications;

use App\Domain\OrderStatus;
use App\Entities\Order;
use App\Repositories\Transaction\TransactionRepository;
use App\Services\Notifications\Contracts\NotificationContract;
use Exception;
use Illuminate\Http\Request;

class MundiPaggNotification implements NotificationContract
{

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * PagSeguroNotification constructor.
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function notify(Request $request)
    {
        $reference = $request->json('OrderReference');

        if ($reference && !empty($reference)) {

            $transactions = $this->transactionRepository->findWhere([
                'pay_reference' => $reference
            ]);

            $status = $request->json("CreditCardTransaction.CreditCardTransactionStatus");
            $capturedAmount = $request->json("CreditCardTransaction.CapturedAmountInCents");
            $refundedAmount = $request->json("CreditCardTransaction.RefundedAmountInCents");
            if(!$refundedAmount){
                $refundedAmount = $request->json("CreditCardTransaction.VoidedAmountInCents");
            }

            if ($transactions->count()) {
                foreach ($transactions as $transaction) {
                    /**
                     * @var $order Order
                     */
                    if ($order = $transaction->order) {

                        $total = number_format($order->total + $order->freight_value, 2, '', '');

                        switch ($status) {
                            case "Captured":
                            case "Paid":
                                if (!in_array($order->status, [OrderStatus::APPROVED, OrderStatus::INTEGRATED])) {
                                    if ($capturedAmount == $total) {
                                        $order->status = OrderStatus::APPROVED;
                                        $order->save();
                                    }
                                }
                                return true;

                            case "Canceled":
                            case "Voided":
                            case "NotAuthorized":
                            case "Refunded":
                                if($order->approvedUpsellOrders->count() == 0){
                                    if ($total == $refundedAmount) {
                                        $order->status = OrderStatus::REFUND;
                                        $order->save();
                                    }
                                }
                                return true;
                        }
                    }
                }
            }
        }

        throw new Exception("(MundiPagg) Order not found : " . $reference);
    }

    /**
     * @param Exception $e
     * @return mixed
     */
    public function log(Exception $e)
    {
        // TODO: Implement log() method.
    }
}