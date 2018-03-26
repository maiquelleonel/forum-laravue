<?php

namespace App\Services\Gateways\Contracts;

use App\Entities\Customer;
use App\Services\Gateways\PaymentResponse;
use Carbon\Carbon;

interface Boleto
{
    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer);

    /**
     * @return Customer $customer
     */
    public function getCustomer();

    /**
     * @param Carbon $date
     * @return $this
     */
    public function setDueDate(Carbon $date = null);

    /**
     * @return Carbon
     */
    public function getDueDate();

    /**
     * @param float $value
     * @return $this
     */
    public function setValue($value);

    /**
     * @return float $value
     */
    public function getValue();

    /**
     * @return PaymentResponse
     */
    public function make();

    /**
     * @param $reference
     * @return PaymentResponse
     */
    public function findBillet($reference);
}