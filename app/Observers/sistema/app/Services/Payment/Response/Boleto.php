<?php

namespace App\Services\Payment\Response;

use Carbon\Carbon;
use App\Entities\Transaction;
use App\Services\Payment\Contracts\BoletoResponse;

class Boleto implements BoletoResponse
{
    /**
     * @var Transaction
     */
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

    public function getLink()
    {
        // Asaas
        if(isset($this->response->boletoUrl)){
            return $this->response->boletoUrl;
        }

        // Boleto Fácil
        if(isset($this->response->data->charges[0]->link)){
            return $this->response->data->charges[0]->link;
        }

        return null;
    }

    public function getInvoiceLink()
    {
        // Asaas
        if(isset($this->response->invoiceUrl)){
            return $this->response->invoiceUrl;
        }

        // Boleto Fácil
        if(isset($this->response->data->charges[0]->checkoutUrl)){
            return $this->response->data->charges[0]->checkoutUrl;
        }

        return null;
    }

    public function getDueDate()
    {
        if( isset( $this->request->dueDate ) ) {
            try {
                return Carbon::createFromFormat("d/m/Y", $this->request->dueDate);
            } catch (\Exception $e) { }
        }

        return Carbon::now()->addDays(2);
    }

    public function getTotal()
    {
        // Asaas
        if (isset($this->response->value)) {
            return $this->response->value;
        }

        // Boleto Fácil
        if (isset($this->request->amount)) {
            return $this->request->amount;
        }

        return 0;
    }

    public function getStatus()
    {
        // Asaas
        if (isset($this->response->status)) {
            return $this->response->status;
        }

        // Boleto Fácil
        if (isset($this->response->success)) {
            return $this->response->success ? "PENDING" : "ERROR";
        }

        return null;
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        $links = [];

        if ($this->getInvoiceLink()) {

            $links["<i class='fa fa-file'></i>"] = (object) [
                "url"=>$this->getInvoiceLink(),
                "attributes" => [
                    "class"     =>"btn btn-xs btn-success",
                    "target"    => "_blank",
                    "title"     => "Exibir Fatura"
                ]
            ];
        }

        if ($link = $this->getLink()) {
            $links["<i class='fa fa-barcode'></i>"] = (object) [
                "url"=>$this->getLink(),
                "attributes" => [
                    "class"     => "btn btn-xs btn-primary",
                    "target"    => "_blank",
                    "title"     => "Exibir Boleto"
                ]
            ];
        }

        return $links;
    }
}