<?php

namespace App\Domain;

class TransactionType
{
    const BOLETO        = "BOLETO";
    const CARTAO        = "CARTAO";
    const TRANSFERENCIA = "TRANSFERENCIA";
    const PAGSEGURO     = "PAGSEGURO";
    const PAYPAL        = "PAYPAL";
}