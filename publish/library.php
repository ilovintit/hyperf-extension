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
    'model' => [
        'default_code_length' => env('MODEL_DEFAULT_CODE_LENGTH', 10)
    ],
    'export' => [
        'oss_prefix' => 'export/'
    ],
    'redis_lock' => [
        'pool' => 'default',
    ],
    'middleware' => [
        'api_signature_key' => env('API_SIGN_KEY'),
        'transmission_encryption' => env('TRAN_ENCRYPT', false)
    ],
    'services' => [
        'wechat' => [
            'official_account' => [
                'default' => [
                    'app_id' => env('WECHAT_OFFICIAL_ACCOUNT_APPID'),
                    'secret' => env('WECHAT_OFFICIAL_ACCOUNT_SECRET'),
                    'token' => env('WECHAT_OFFICIAL_ACCOUNT_TOKEN'),
                    'aes_key' => env('WECHAT_OFFICIAL_ACCOUNT_AES_KEY'),
                ],
            ],
            'mini_program' => [
                'default' => [
                    'app_id' => env('WECHAT_MINI_PROGRAM_APPID'),
                    'secret' => env('WECHAT_MINI_PROGRAM_SECRET'),
                    'token' => env('WECHAT_MINI_PROGRAM_TOKEN'),
                    'aes_key' => env('WECHAT_MINI_PROGRAM_AES_KEY'),
                ],
            ],
            'payment' => [
                'default' => [
                    'app_id' => env('WECHAT_PAYMENT_APPID'),
                    'mch_id' => env('WECHAT_PAYMENT_MCH_ID'),
                    'key' => env('WECHAT_PAYMENT_KEY'),
                    'cert_path' => '',
                    'key_path' => '',
                    'notify_url' => env('WECHAT_PAYMENT_NOTIFY_URL'),
                ],
            ]
        ]
    ]
];
