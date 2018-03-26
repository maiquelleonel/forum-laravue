<?php

    return [
        //para testes criar as chaves no recaptcha com localhost, 127.0.0.1 e 192.168.0.250
        //Salvar no Env.
        'secret'            => env('RECAPTCHA_SECRET'  ,'6LeNViMUAAAAAGZDxTCAGH6cbuWnFSPBIGTuSPnu'),
        'site_key'          => env('RECAPTCHA_SITE_KEY','6LeNViMUAAAAADK1pN-MGKGdvRksvmVmspXzBGmw'),
        'max_post_requests' => env('RECAPTCHA_MAX_POST_REQUESTS', 3),
        'max_db_entries'    => env('RECAPTCHA_MAX_DB_ENTRIES', 3)
    ];
