<?php

return [
    'brand'        => env('JELAJAH_BRAND_NAME', 'BeDaie'),
    'org'          => env('JELAJAH_ORG_NAME', 'Dakwah Digital Network'),
    'tagline'      => 'Membawa Ilmu, Menghidupkan Ummah.',
    'slogan'       => 'Dari Masjid ke Masjid, Dari Hati ke Hati.',
    'motto'        => '1 Rumah, 1 Daie',
    'website'      => 'https://bedaie.com.my/',

    'support' => [
        'phone' => env('JELAJAH_SUPPORT_PHONE', '60123456789'),
        'email' => env('JELAJAH_SUPPORT_EMAIL', 'jelajah@bedaie.com.my'),
    ],

    'reference' => [
        'application' => 'BDJ-P',
        'registration' => 'BDJ-R',
        'certificate' => 'BDJ',
        'outreach' => 'BDJ-S',
    ],

    'whatsapp' => [
        'enabled'   => env('WHATSAPP_ENABLED', false),
        'driver'    => env('WHATSAPP_DRIVER', 'log'),
        'base_url'  => env('WHATSAPP_BASE_URL'),
        'api_key'   => env('WHATSAPP_API_KEY'),
        'session'   => env('WHATSAPP_SESSION', 'default'),
    ],

    'payments' => [
        'default'  => env('PAYMENT_GATEWAY', 'manual'),
        'currency' => 'MYR',
        'gateways' => [
            'manual' => [
                'label'      => 'Pindahan Bank / Manual',
                'bank_name'  => env('PAYMENT_BANK_NAME', 'Maybank'),
                'account_no' => env('PAYMENT_BANK_ACCOUNT', '000000000000'),
                'account_name' => env('PAYMENT_BANK_HOLDER', 'Dakwah Digital Network'),
            ],
            'bayarcash' => [
                'label'       => 'FPX / DuitNow (BayarCash)',
                'api_token'   => env('BAYARCASH_API_TOKEN'),
                'portal_key'  => env('BAYARCASH_PORTAL_KEY'),
                'secret_key'  => env('BAYARCASH_SECRET_KEY'),
                'sandbox'     => env('BAYARCASH_SANDBOX', true),
            ],
        ],
    ],

    'reminders' => [
        ['key' => 'reminder_7_days', 'offset_hours' => 168],
        ['key' => 'reminder_1_day',  'offset_hours' => 24],
        ['key' => 'reminder_2_hours','offset_hours' => 2],
    ],
];
