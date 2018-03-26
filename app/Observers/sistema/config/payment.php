<?php

return [

    'flags' => [
        'stone' => [
            'visa', 'mastercard', 'hiper'
        ],

        'cielo' => [
            'visa', 'mastercard', 'jcb', 'amex', 'dinners', 'discover', 'elo'
        ],

        'redecard' => [
            'visa', 'mastercard', 'jcb', 'amex', 'dinners', 'discover', 'elo', 'hiper'
        ]
    ],

    "gateways" => [
        "Boleto" => [
            "Asaas"         => "Asaas",
            "BoletoFacil"   => "BoletoFacil"
        ],

        "CreditCard" => [
            "mundipagg"     => "MundiPagg",
            "stripe"        => "Stripe",
        ]
    ],

    'types' => [
        'Boleto'    => 'Asaas',
        'CreditCard'=> 'MundiPagg',
        'Pagseguro' => 'Pagseguro'
    ],

    "environments" => [
        "asaas" => [
            "homologacao"=> "HOMOLOGAÇÃO",
            "production" => "PRODUÇÃO"
        ],

        "boleto_facil" => [
            "sandbox"   => "HOMOLOGAÇÃO",
            "production"=> "PRODUÇÃO"
        ],

        "mundipagg" => [
            "SANDBOX"    => "HOMOLOGAÇÃO",
            "PRODUCTION" => "PRODUÇÃO",
        ],

        "pagseguro" => [
            "sandbox"    => "HOMOLOGAÇÃO",
            "production" => "PRODUÇÃO",
        ],

        "paypal" => [
            "sandbox"    => "HOMOLOGAÇÃO",
            "live"       => "PRODUÇÃO",
        ]
    ]
];