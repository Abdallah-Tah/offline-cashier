# Installation

## Requirements

- PHP 8.1+
- Laravel 10.0+
- Composer

## Installation Steps

1. Install the package via Composer:
```bash
composer require amohamed/offline-cashier
```

2. Publish the configuration and assets:

```bash
php artisan vendor:publish --provider="AMohamed\OfflineCashier\OfflineCashierServiceProvider"
```

This will publish:
- Configuration file: `config/offline-cashier.php`
- Migrations
- Views

3. Run the migrations:

```bash
php artisan migrate
```

4. Add the `HasSubscriptions` trait to your User model:

```php
use AMohamed\OfflineCashier\Traits\HasSubscriptions;

class User extends Authenticatable
{
    use HasSubscriptions;
}
```

5. Configure your environment variables in `.env`:

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

3. The package includes automatic feature assignment for subscriptions, which is part of the core functionality.

## Configuration

### Models

You can customize the model classes in `config/offline-cashier.php`:

```php
'models' => [
    'user' => \App\Models\User::class,
    'subscription' => \AMohamed\OfflineCashier\Models\Subscription::class,
    'plan' => \AMohamed\OfflineCashier\Models\Plan::class,
    'payment' => \AMohamed\OfflineCashier\Models\Payment::class,
    'invoice' => \AMohamed\OfflineCashier\Models\Invoice::class,
],
```

### Payment Methods

Configure available payment methods:

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

Configure which notifications to send:

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

## Next Steps

- Read the [Basic Usage Guide](basic-usage.md)
- Learn about [Payment Methods](payment-methods.md)
- Configure [Events & Notifications](events-notifications.md)
- Set up [Stripe Integration](stripe-integration.md)
