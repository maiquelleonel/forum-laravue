<?php

namespace App\Services\Payment\Response;

use App\Entities\Transaction;
use App\Services\Payment\Contracts\CreditCardResponse;

class CreditCard implements CreditCardResponse
{

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var \stdClass
     */
    private $response;

    /**
     * @var \stdClass
     */
    private $request;

    /**
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
        $this->response = $transaction->response;
        $this->request  = $transaction->request;
    }

    public function getBrand()
    {
        // MUNDIPAGG RESPONSE
        if($this->response && $this->request) {
            if (isset($this->response->CreditCardTransactionResultCollection[0]->CreditCard->CreditCardBrand)) {
                return $this->response->CreditCardTransactionResultCollection[0]->CreditCard->CreditCardBrand;
            }
            if (isset($this->request->CreditCardTransactionCollection[0]->CreditCard->CreditCardBrand)) {
                return $this->request->CreditCardTransactionCollection[0]->CreditCard->CreditCardBrand;
            }
        }

        return null;
    }

    public function getCardNumber()
    {
        // MUNDIPAGG RESPONSE
        if($this->response && $this->request) {
            if (isset($this->response->CreditCardTransactionResultCollection[0]->CreditCard->MaskedCreditCardNumber)) {
                return $this->response->CreditCardTransactionResultCollection[0]->CreditCard->MaskedCreditCardNumber;
            }
            if (isset($this->request->CreditCardTransactionCollection[0]->CreditCard->CreditCardNumber)) {
                return $this->request->CreditCardTransactionCollection[0]->CreditCard->CreditCardNumber;
            }
        }

        return null;
    }

    public function getInstallments()
    {
        // MUNDIPAGG RESPONSE
        if($this->response && $this->request) {
            if (isset($this->response->CreditCardTransactionResultCollection[0]->InstallmentCount)) {
                return $this->response->CreditCardTransactionResultCollection[0]->InstallmentCount;
            }
            if (isset($this->request->CreditCardTransactionCollection[0]->InstallmentCount)) {
                return $this->request->CreditCardTransactionCollection[0]->InstallmentCount;
            }
        }

        return null;
    }

    public function getTotal()
    {
        // MUNDIPAGG RESPONSE
        if($this->response OR $this->request) {
            if (isset($this->response->CreditCardTransactionResultCollection[0]->AmountInCents)) {
                return $this->parseAmount(
                    $this->response->CreditCardTransactionResultCollection[0]->AmountInCents
                );
            }
            if (isset($this->request->CreditCardTransactionCollection[0]->AmountInCents)) {
                return $this->parseAmount(
                    $this->request->CreditCardTransactionCollection[0]->AmountInCents
                );
            }
        }

        return null;
    }

    public function getHolderName()
    {
        if(isset($this->request->CreditCardTransactionCollection[0]->CreditCard->HolderName)) {
            return $this->request->CreditCardTransactionCollection[0]->CreditCard->HolderName;
        }

        return null;
    }

    public function parseAmount($valueInCents)
    {
        return substr_replace($valueInCents, ".", -2, 0);
    }

    public function getPaymentKey()
    {
        // MundiPagg
        if(isset($this->response->OrderResult->OrderKey)) {
            return $this->response->OrderResult->OrderKey;
        }

        return null;
    }

    public function getPaymentToken()
    {
        if(isset($this->response->CreditCardTransactionResultCollection[0]->CreditCard->InstantBuyKey)) {
            return $this->response->CreditCardTransactionResultCollection[0]->CreditCard->InstantBuyKey;
        }

        return null;
    }

    public function getStoreToken()
    {
        // MundiPagg MerchantKey
        if (isset($this->response->MerchantKey)) {
            return $this->response->MerchantKey;
        }

        return null;
    }

    public function getStatus()
    {
        // MUNDIPAGG STATUS RESPONSE
        if (isset($this->response->CreditCardTransactionResultCollection[0]->CreditCardTransactionStatus)) {
            return $this->response->CreditCardTransactionResultCollection[0]->CreditCardTransactionStatus;
        }

        // MUNDIPAGG ERROR RESPONSE
        if (isset($this->response->errorCollection->ErrorItemCollection[0]->SeverityCode)) {
            return $this->response->errorCollection->ErrorItemCollection[0]->SeverityCode;
        }

        // MUNDIPAGG REQUEST ERROR RESPONSE
        if(isset($this->response->responseBody) && str_contains($this->response->responseBody, "Request Error")){
            return "RequestError";
        }
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return [];
    }
}