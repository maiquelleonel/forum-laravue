<?php

namespace App\Services\Payment\Contracts;

interface PayPalResponse extends Response
{
    public function getCheckoutLink();
}