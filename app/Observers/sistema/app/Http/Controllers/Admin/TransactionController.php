<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\Transaction\TransactionRepository;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Entities\Order;
use App\Support\SiteSettings;

class TransactionController extends Controller
{
    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * TransactionController constructor.
     * @param SiteSettings $siteSettings
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(SiteSettings $siteSettings,
                                TransactionRepository $transactionRepository)
    {
        parent::__construct($siteSettings);
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Refund Ca.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function refund($id)
    {
        $response = $this->transactionRepository->refund($id);

        if($response->getStatus()){
            $transaction = $this->transactionRepository->with("order")->find($id);
            if($transaction && $transaction->type != "shipping"){
                $transaction->order->update([
                    "status" => Order::STATUS_VOIDED
                ]);
            }
            return back()->with("success", $response->getMessage());
        }

        return back()->with("error", "Esta transação já foi estornada");
    }

    /**
     * Capture authorized transaction.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function capture($id)
    {
        $response = $this->transactionRepository->capture($id);

        if($response->getStatus()){
            $transaction = $this->transactionRepository->with("order")->find($id);
            if($transaction && $transaction->type != "shipping"){
                $transaction->order->update([
                    "status" => Order::STATUS_APPROVED
                ]);
            }
            return back()->with("success", $response->getMessage());
        }

        return back()->with("error", "Esta transação já foi capturada");
    }
}
