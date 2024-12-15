# OfflineCashier

A Laravel package for managing offline and online subscriptions with support for cash, check, bank transfer, and Stripe payments.

## Features

- Subscription Management (create, cancel, resume, change plans)
- Multiple Payment Methods (cash, check, bank transfer, Stripe)
- Invoice Generation with PDF Support
- Email Notifications
- Stripe Integration with Webhook Support
- Customizable Views
- Event System
- Comprehensive Test Suite

## Requirements

- PHP 8.1+
- Laravel 10.0+
- Composer

## Installation

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
],
```

### Testing

The package includes a comprehensive test suite. To run the tests:

```bash
composer test
```

All tests are passing:

![Test Results](tests-success.png)

## Documentation

For detailed documentation, please refer to the [documentation](docs) directory:

1. [Installation](docs/installation.md)
2. [Configuration](docs/configuration.md)
3. [Basic Usage](docs/basic-usage.md)
4. [Payment Methods](docs/payment-methods.md)
5. [Events & Notifications](docs/events-notifications.md)
6. [Stripe Integration](docs/stripe-integration.md)
7. [Invoice Generation](docs/invoice-generation.md)
8. [Testing](docs/testing.md)

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md). 