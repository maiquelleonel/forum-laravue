<?php

namespace App\Services\Payment\Contracts;

interface PagSeguroResponse extends Response
{
    public function getCheckoutLink();
}