<?php

return [
    'routes' => [
        'prefix' => 'offline-cashier',
        'middleware' => ['web', 'auth'],
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'currency' => env('CASHIER_CURRENCY', 'usd'),
    'currency_symbol' => env('CASHIER_CURRENCY_SYMBOL', '$'),

    'models' => [
        'user' => \App\Models\User::class,
        'subscription' => \AMohamed\OfflineCashier\Models\Subscription::class,
        'plan' => \AMohamed\OfflineCashier\Models\Plan::class,
        'payment' => \AMohamed\OfflineCashier\Models\Payment::class,
        'invoice' => \AMohamed\OfflineCashier\Models\Invoice::class,
    ],

    'tables' => [
        'subscriptions' => 'subscriptions',
        'subscription_items' => 'subscription_items',
        'plans' => 'plans',
        'payments' => 'payments',
        'invoices' => 'invoices',
    ],

    'payment_methods' => [
        'cash' => true,
        'check' => true,
        'bank_transfer' => true,
        'stripe' => true,
        'settings' => [
            'cash' => [
                'requires_reference' => true,
                'reference_label' => 'Receipt Number',
            ],
            'check' => [
                'requires_reference' => true,
                'reference_label' => 'Check Number',
            ],
            'bank_transfer' => [
                'requires_reference' => true,
                'reference_label' => 'Transaction ID',
            ],
        ],
    ],

    'notifications' => [
        'channels' => ['mail', 'database'],
        'payment_success' => true,
        'payment_failed' => true,
        'subscription_created' => true,
        'subscription_canceled' => true,
        'subscription_expired' => true,
        'subscription_renewed' => true,
        'trial_ending' => true,
        'invoice_paid' => true,
    ],

    'pdf' => [
        'paper_size' => 'a4',
        'font_family' => 'helvetica',
        'logo_path' => null,
        'company_details' => [
            'name' => env('APP_NAME'),
            'address' => env('COMPANY_ADDRESS'),
            'phone' => env('COMPANY_PHONE'),
            'email' => env('COMPANY_EMAIL'),
            'vat' => env('COMPANY_VAT'),
        ],
    ],
]; 