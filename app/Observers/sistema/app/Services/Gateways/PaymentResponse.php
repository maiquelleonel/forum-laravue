<?php

namespace App\Services\Gateways;

use JsonSerializable;

class PaymentResponse implements JsonSerializable
{
    /**
     * @var bool
     */
    private $status;

    /**
     * @var string
     */
    private $message;

        /**
     * @var string
     */
    private $data;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var mixed
     */
    private $transactionResponse;

    /**
     * @var mixed
     */
    private $transactionRequest;

    /**
     * PaymentResponse constructor.
     * @param boolean $status
     * @param $statusCode
     * @param string $message
     * @param $data
     * @param $transactionRequest
     * @param $transactionResponse
     */
    public function __construct($status, $statusCode, $message, $data, $transactionRequest, $transactionResponse)
    {
        $this->status = $status;
        $this->statusCode = $statusCode;
        $this->message = $message;
        $this->data = $data;
        $this->transactionRequest = $transactionRequest;
        $this->transactionResponse = $transactionResponse;
    }

    /**
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return mixed
     */
    public function getTransactionResponse()
    {
        return $this->transactionResponse;
    }

    /**
     * @return mixed
     */
    public function getTransactionRequest()
    {
        return $this->transactionRequest;
    }

    public function getTransactionStatus()
    {
        $response = json_decode($this->getTransactionResponse());

        // MundiPagg Response Identifier
        if (isset($response->SaleDataCollection[0]->CreditCardTransactionDataCollection)) {
            $transaction = last($response->SaleDataCollection[0]->CreditCardTransactionDataCollection);
            return $transaction->CreditCardTransactionStatus;
        }

        return null;
    }

    public function getRefundedValue()
    {
        $response = json_decode($this->getTransactionResponse());

        // MundiPagg Response Identifier
        if (isset($response->SaleDataCollection[0]->CreditCardTransactionDataCollection[0]->RefundedAmountInCents)) {
            $refunded = $response->SaleDataCollection[0]->CreditCardTransactionDataCollection[0]->RefundedAmountInCents;
            return (float) substr_replace($refunded, ".", -2, 0);
        }

        return 0;
    }

    public function getTotalValue()
    {
        $response = json_decode($this->getTransactionResponse());

        // MundiPagg Response Identifier
        if (isset($response->SaleDataCollection[0]->CreditCardTransactionDataCollection[0]->CapturedAmountInCents)) {
            $amount = $response->SaleDataCollection[0]->CreditCardTransactionDataCollection[0]->CapturedAmountInCents;
            $amount = (float) substr_replace($amount, ".", -2, 0);
            if ($amount > 0) {
                return $amount;
            }
        }

        // MundiPagg Response Identifier
        if (isset($response->SaleDataCollection[0]->CreditCardTransactionDataCollection[0]->AmountInCents)) {
            $amount = $response->SaleDataCollection[0]->CreditCardTransactionDataCollection[0]->AmountInCents;
            return (float) substr_replace($amount, ".", -2, 0);
        }

        return 0;
    }

    public function getIdentifier()
    {
        $response = json_decode($this->getTransactionResponse());
        $request = json_decode($this->getTransactionRequest());

        // MundiPagg Response Identifier
        if (isset($response->OrderResult->OrderReference)) {
            return $response->OrderResult->OrderReference;
        }

        // MundiPagg Request Identifier
        if (isset($request->Order->OrderReference)) {
            return $request->Order->OrderReference;
        }

        // MundiPagg
        if (isset($request->Order->OrderReference)) {
            return $request->Order->OrderReference;
        }

        // Asaas
        if (isset($response->id)){
            return $response->id;
        }

        // Boleto Fácil
        if (isset($request->reference)) {
            return $request->reference;
        }

        return null;
    }

    public function getPaymentMethod()
    {
        $response = json_decode($this->getTransactionResponse());
        // MundiPagg
        if (isset($response->CreditCardTransactionResultCollection[0]->CreditCardOperation)) {
            return $response->CreditCardTransactionResultCollection[0]->CreditCardOperation;
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getPaymentToken()
    {
        $response = json_decode($this->getTransactionResponse());

        // MundiPagg CreditCard Token
        if (isset($response->CreditCardTransactionResultCollection[0]->CreditCard->InstantBuyKey)) {
            return $response->CreditCardTransactionResultCollection[0]->CreditCard->InstantBuyKey;
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getTransactionToken()
    {
        $response = json_decode($this->getTransactionResponse());

        // MundiPagg Order Key
        if (isset($response->OrderResult->OrderKey)) {
            return $response->OrderResult->OrderKey;
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getStoreToken()
    {
        $response = json_decode($this->getTransactionResponse());

        // MundiPagg MerchantKey
        if (isset($response->MerchantKey)) {
            return $response->MerchantKey;
        }

        return null;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return (Object)[
            'statusCode'            => $this->getStatusCode(),
            'status'                => $this->getStatus(),
            'message'               => $this->getMessage(),
            'data'                  => $this->getData(),
            'transactionRequest'    => $this->getTransactionRequest(),
            'transactionResponse'   => $this->getTransactionResponse()
        ];
    }
}