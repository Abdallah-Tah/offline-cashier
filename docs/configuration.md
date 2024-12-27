# Configuration

This document outlines all the configuration options available in the OfflineCashier package.

## Publishing Configuration

After installing the package, publish the configuration file using:

```bash
php artisan vendor:publish --provider="AMohamed\OfflineCashier\OfflineCashierServiceProvider"
```

This will create a `config/offline-cashier.php` file in your application.

## Configuration Options

### Routes Configuration

Configure the route prefix and middleware:

```php
'routes' => [
    'prefix' => 'offline-cashier',
    'middleware' => ['web', 'auth'],
],
```

### Stripe Configuration

Configure Stripe integration (optional):

```php
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook' => [
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
        'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],
],
```

### Currency Settings

Set your default currency and symbol:

```php
'currency' => env('CASHIER_CURRENCY', 'usd'),
'currency_symbol' => env('CASHIER_CURRENCY_SYMBOL', '$'),
```

### Model Configuration

Customize the model classes used by the package:

```php
'models' => [
    'user' => \App\Models\User::class,
    'subscription' => \AMohamed\OfflineCashier\Models\Subscription::class,
    'plan' => \AMohamed\OfflineCashier\Models\Plan::class,
    'payment' => \AMohamed\OfflineCashier\Models\Payment::class,
    'invoice' => \AMohamed\OfflineCashier\Models\Invoice::class,
],
```

### Database Tables

Configure table names:

```php
'tables' => [
    'subscriptions' => 'subscriptions',
    'subscription_items' => 'subscription_items',
    'plans' => 'plans',
    'payments' => 'payments',
    'invoices' => 'invoices',
],
```

### Payment Methods

Configure available payment methods and their settings:

```php
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
```

### Notifications

Configure notification settings:

```php
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
```

### PDF Configuration

Configure PDF generation settings:

```php
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
```

### Feature Assignment Configuration

The package provides automatic feature assignment for subscriptions based on the selected plan. You can customize this behavior by overriding the `customizeFeatures` method in the `SubscriptionService`.

To enable or disable automatic feature assignment, or to customize the logic, modify the `config/offline-cashier.php` file as needed.

## Environment Variables

Add these variables to your `.env` file:

```env
# Stripe Configuration (optional)
STRIPE_KEY=your-stripe-publishable-key
STRIPE_SECRET=your-stripe-secret-key
STRIPE_WEBHOOK_SECRET=your-webhook-secret

# Currency Configuration
CASHIER_CURRENCY=usd
CASHIER_CURRENCY_SYMBOL=$

# Company Details for Invoices
COMPANY_NAME="Your Company Name"
COMPANY_ADDRESS="Your Company Address"
COMPANY_PHONE="Your Phone Number"
COMPANY_EMAIL="your@email.com"
COMPANY_VAT="Your VAT Number"
```

## Customizing Models

### User Model

Add the `HasSubscriptions` trait to your User model:

```php
use AMohamed\OfflineCashier\Traits\HasSubscriptions;

class User extends Authenticatable
{
    use HasSubscriptions;
}
```

### Custom Models

If you want to extend or replace any of the default models, create your own model and update the configuration:

```php
use AMohamed\OfflineCashier\Models\Subscription as BaseSubscription;

class Subscription extends BaseSubscription
{
    // Your customizations here
}
```

Then update your config:

```php
'models' => [
    'subscription' => \App\Models\Subscription::class,
],
```

## Events and Listeners

The package dispatches several events that you can listen for:

- `SubscriptionCreated`
- `SubscriptionCanceled`
- `SubscriptionResumed`
- `PaymentReceived`

Register your listeners in your `EventServiceProvider`:

```php
protected $listen = [
    \AMohamed\OfflineCashier\Events\PaymentReceived::class => [
        \App\Listeners\YourPaymentListener::class,
    ],
];
```

## Next Steps

- Learn about [Basic Usage](basic-usage.md)
- Configure [Payment Methods](payment-methods.md)
- Set up [Stripe Integration](stripe-integration.md)
- Customize [Invoice Generation](invoice-generation.md) 