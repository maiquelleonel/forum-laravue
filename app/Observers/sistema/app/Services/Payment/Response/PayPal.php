<?php

namespace App\Services\Payment\Response;


use App\Domain\OrderStatus;
use App\Entities\Transaction;
use App\Services\Payment\Contracts\PayPalResponse;

class PayPal implements PayPalResponse
{
    private $transaction;

    private $request;

    private $response;

    /**
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
        $this->request     = $transaction->request;
        $this->response    = $transaction->response;
    }

    /**
     * @return string
     */
    public function getCheckoutLink()
    {
        // TODO: Implement getCheckoutLink() method.
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        if(isset($this->response->state)){
            return $this->response->state;
        }

        return OrderStatus::PENDING;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        if(isset($this->response->transactions[0]->amount->total)){
            return $this->response->transactions[0]->amount->total;
        }

        if(isset($this->request->transactions[0]->amount->total)){
            return $this->request->transactions[0]->amount->total;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return [];
    }
}