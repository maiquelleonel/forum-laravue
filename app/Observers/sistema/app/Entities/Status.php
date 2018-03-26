<?php

namespace App\Entities;

class Status
{
    const APPROVED      = 'aprovado';
    const AUTHORIZED    = 'autorizado';
    const INTEGRATED    = 'integrado';
    const CANCELED      = 'cancelado';
    const RETRY         = 'retentativa';
    const PENDING       = 'pendente';
}