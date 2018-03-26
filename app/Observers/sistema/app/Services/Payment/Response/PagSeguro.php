<?php

namespace App\Services\Payment\Response;


use App\Domain\OrderStatus;
use App\Entities\Transaction;
use App\Services\Payment\Contracts\PagSeguroResponse;

class PagSeguro implements PagSeguroResponse
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
        if(isset($this->response->details->status) && $this->response->details->status == 3){
            return OrderStatus::APPROVED;
        }

        return OrderStatus::PENDING;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        $amount = isset($this->request->order->items->items->amount)
                        ? $this->request->order->items->items->amount : 0;

        if(isset($this->request->items)){
            foreach($this->request->items as $item){
                $amount+= $item->amount * $item->quantity;
            }
        }

        if ($amount == 0) {
            if (isset($this->response->payment->grossAmount)) {
                $amount = $this->response->payment->grossAmount;
            }
        }

        $shipping = isset($this->request->order->shipping->cost)
                        ? $this->request->order->shipping->cost : 0;

        if($shipping === 0 && isset($this->request->shipping->cost)) {
            $shipping = $this->request->shipping->cost;
        }

        if ($shipping === 0) {
            if (isset($this->response->shipping->cost)) {
                $shipping = $this->response->shipping->cost;
            }
        }

        return $amount + $shipping;
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return [];
    }
}