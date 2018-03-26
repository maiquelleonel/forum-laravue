<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Driver
    |--------------------------------------------------------------------------
    |
    | Default SMS Driver
    |
    | Supported: "fake", "plivo"
    |
    */

    'driver' => env('SMS_DRIVER', "fake"),

    /*
    |--------------------------------------------------------------------------
    | SMS Api Key
    |--------------------------------------------------------------------------
    |
    | Api key if your drive require api access
    |
    */
    'api_key'=> env('SMS_APIKEY', null),

    /*
    |--------------------------------------------------------------------------
    | SMS username
    |--------------------------------------------------------------------------
    |
    | Username to connect with your drive
    |
    */
    'username'=> env('SMS_USERNAME', null),

    /*
    |--------------------------------------------------------------------------
    | SMS password
    |--------------------------------------------------------------------------
    |
    | password to connect with your drive
    |
    */
    'password'=> env('SMS_PASSWORD', null),

    /*
    |--------------------------------------------------------------------------
    | SMS number source
    |--------------------------------------------------------------------------
    |
    | Number source to sms
    |
    */
    'source'=> env('SMS_SOURCE', null),

    /*
    |--------------------------------------------------------------------------
    | SMS drivers
    |--------------------------------------------------------------------------
    |
    | Here are each of the drives setup for your application.
    |
    */
    'drivers' => [
        'plivo'   => \App\Services\Sms\Plivo::class,
        'locasms' => \App\Services\Sms\LocaSms::class,
        'fake'    => \App\Services\Sms\Fake::class
    ]
];