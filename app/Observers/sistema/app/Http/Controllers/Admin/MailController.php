<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\Orders\OrderRepository;
use App\Repositories\Transaction\TransactionRepository;
use App\Services\MailService;
use App\Support\SiteSettings;

class MailController extends Controller
{
    /**
     * @var MailService
     */
    private $mailService;
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * MailController constructor.
     * @param SiteSettings $siteSettings
     * @param OrderRepository $orderRepository
     * @param MailService $mailService
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(SiteSettings $siteSettings,
                                OrderRepository $orderRepository,
                                MailService $mailService,
                                TransactionRepository $transactionRepository)
    {
        parent::__construct($siteSettings);
        $this->mailService = $mailService;
        $this->orderRepository = $orderRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function resendBillet( $transaction_id )
    {

        $transaction = $this->transactionRepository->with('order.customer', 'order.items')->find($transaction_id);

        if( $transaction && isset($transaction->response_json->boletoUrl) && $transaction->order->customer ) {

            try {
                $this->mailService->resendBillet( $transaction );
                session()->flash('success', 'Email com o boleto reenviado com sucesso!');
            } catch (\Exception $e) {
                session()->flash('error', 'Não foi possível enviar o email, ' . $e->getMessage());
            }

        } else {
            session()->flash('message', 'Não foi possível localizar o link do boleto para esta transação');
        }

        return back();
    }
}