<?php

namespace App\Services\Gateways\Contracts;

use App\Entities\Customer;
use App\Services\Gateways\PaymentResponse;
use Faker\Provider\it_CH\Payment;
use Illuminate\Support\Collection;

/**
 * Created by PhpStorm.
 * User: Daniel Lima
 * Date: 2/5/16
 * Time: 11:33 AM
 */
interface CreditCard
{
    /**
     * Set total value in cents
     * @param $totalInCents
     * @return $this
     */
    public function setTotal($totalInCents);

    /**
     * Set the installments transaction
     * @param $installments
     * @return $this
     */
    public function setInstallments($installments);

    /**
     * Set the customer
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer);

    /**
     * @param $card
     * @return $this
     */
    public function setCreditCard($card);

    /**
     * @return mixed
     */
    public function getCreditCard();

    /**
     * Set the payment operation
     * @param $operation
     * @return mixed
     */
    public function setOperation($operation);

    /**
     * @return PaymentResponse
     */
    public function makeAuthAndCapture();

    /**
     * @return PaymentResponse
     */
    public function makeAuth();

    /**
     * @return PaymentResponse
     */
    public function makeCapture();

    /**
     * @param string $acquirerOrderKey
     * @param $storeKey
     * @return PaymentResponse
     */
    public function makeCancel($acquirerOrderKey, $storeKey);

    /**
     * @param $identifier
     * @return $this
     */
    public function setIdentifier($identifier);

    /**
     * @param String $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @param Collection $items
     * @return $this
     */
    public function setCartItems(Collection $items);

    /**
     * @param $freightValueInCents
     * @return $this
     */
    public function setFreightValue($freightValueInCents);

    /**
     * @return boolean
     */
    public function hasAntiFraud();

    /**
     * @param bool $useAntiFraud
     * @return $this
     */
    public function useAntiFraud($useAntiFraud = false);

    /**
     * @param $transactionKey
     * @param null $storeToken
     * @return PaymentResponse
     */
    public function findByKey($transactionKey, $storeToken = null);

    /**
     * @param $reference
     * @param null $storeToken
     * @return PaymentResponse
     */
    public function findByReference($reference, $storeToken = null);
}