<?php
return [
    'ead' => [
        'default' => 'basic',
        'secrets' => [
            'basic' => [
                'cipher' => env('AES_CIPHER', 'AES-128-CBC'),
                'key' => env('AES_KEY'),
                'iv' => env('AES_IV'),
            ]
        ]
    ],
    'export' => [
        'oss_prefix' => 'export/'
    ],
    'redis_lock' => [
        'pool' => 'default',
    ],
    'middleware' => [
        'transmission_encryption' => env('TRAN_ENCRYPT', false)
    ]
];
