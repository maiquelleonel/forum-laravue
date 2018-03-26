<?php

namespace App\Services\Payment\Contracts;

use App\Entities\Transaction;

interface Response
{
    /**
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return float
     */
    public function getTotal();

    /**
     * @return array
     */
    public function getLinks();
}