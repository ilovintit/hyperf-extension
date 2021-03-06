<?php
return [
    'ead' => [
        'default' => 'basic',
        'secrets' => [
            'basic' => [
                'cipher' => config('AES_CIPHER', 'AES-128-CBC'),
                'key' => config('AES_KEY'),
                'iv' => config('AES_IV'),
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
        'transmission_encryption' => config('TRAN_ENCRYPT', false)
    ]
];
