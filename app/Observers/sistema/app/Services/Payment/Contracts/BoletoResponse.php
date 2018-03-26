<?php

namespace App\Services\Payment\Contracts;

interface BoletoResponse extends Response
{
    public function getLink();

    public function getDueDate();
}