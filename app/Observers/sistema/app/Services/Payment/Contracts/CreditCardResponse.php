<?php

namespace App\Services\Payment\Contracts;

interface CreditCardResponse extends Response
{
    public function getPaymentKey();

    public function getPaymentToken();

    public function getBrand();

    public function getCardNumber();

    public function getInstallments();
}