<?php

return [

    // ORDERS STATUS

    \App\Domain\OrderStatus::APPROVED => [
        "label" => "success",
        "icon"  => "check",
        "text"  => "Aprovado"
    ],

    \App\Domain\OrderStatus::AUTHORIZED => [
        "label" => "primary",
        "icon"  => "user-secret",
        "text"  => "Análise Antifraude"
    ],

    \App\Domain\OrderStatus::INTEGRATED => [
        "label" => "info",
        "icon"  => "thumbs-up",
        "text"  => "Integrado"
    ],

    \App\Domain\OrderStatus::PENDING_INTEGRATION_IN_ANALYZE => [
        "label" => "info",
        "icon"  => "exclamation-triangle",
        "text"  => "Pendente Integração (Em Análise)"
    ],

    \App\Domain\OrderStatus::PENDING_INTEGRATION => [
        "label" => "info",
        "icon"  => "exclamation-triangle",
        "text"  => "Pendente Integração"
    ],

    \App\Domain\OrderStatus::CANCELED => [
        "label" => "danger",
        "icon"  => "close",
        "text"  => "Cancelado"
    ],

    \App\Domain\OrderStatus::PENDING => [
        "label" => "warning",
        "icon"  => "retweet",
        "text"  => "Pendente"
    ],

    \App\Domain\OrderStatus::REFUND => [
        "label" => "danger",
        "icon"  => "mail-reply",
        "text"  => "Estornado"
    ],

    // COMMISSIONS STATUS

    \App\Domain\CommissionStatus::APPROVED => [
        "label" => "primary",
        "icon"  => "check-circle-o"
    ],

    \App\Domain\CommissionStatus::PENDING => [
        "label" => "warning",
        "icon"  => "clock-o"
    ],

    \App\Domain\CommissionStatus::PAID => [
        "label" => "success",
        "icon"  => "dollar"
    ],

    \App\Domain\CommissionStatus::SHAVED => [
        "label" => "danger",
        "icon"  => "ban"
    ]

];