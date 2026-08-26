<?php

return [
    'gateway' => [
        'url' => env('SMS_GATEWAY_URL', 'https://emg.ctm-mobile.com:10081/servlet/SendSMS'),
        'username' => env('SMS_USERNAME'),
        'password' => env('SMS_PASSWORD'),
        'from' => env('SMS_FROM'),
    ],

    'timeout' => env('SMS_TIMEOUT', 5),
    'retry' => env('SMS_RETRY', 1),
];