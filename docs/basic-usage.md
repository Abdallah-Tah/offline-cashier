# Basic Usage

This guide covers the basic usage of the OfflineCashier package, including managing subscriptions, handling payments, and working with plans.

## Managing Plans

### Creating Plans

```php
use AMohamed\OfflineCashier\Models\Plan;

// Create a basic monthly plan
$plan = Plan::create([
    'name' => 'Basic Plan',
    'description' => 'Basic features for small teams',
    'price' => 29.99,
    'billing_interval' => 'month',
    'trial_period_days' => 14, // Optional trial period
    'features' => ['feature1', 'feature2', 'feature3'],
]);

// Create a yearly plan
$yearlyPlan = Plan::create([
    'name' => 'Premium Annual',
    'description' => 'Premium features with yearly discount',
    'price' => 299.99,
    'billing_interval' => 'year',
]);
```

### Retrieving Plans

```php
// Get all active plans
$plans = Plan::all();

// Get plans with trial
$trialPlans = Plan::whereNotNull('trial_period_days')->get();

// Get monthly plans
$monthlyPlans = Plan::where('billing_interval', 'month')->get();
```

## Managing Subscriptions

### Creating a Subscription

```php
use AMohamed\OfflineCashier\Facades\OfflineCashier;

// Create a subscription for a user
$subscription = OfflineCashier::subscribe($user, $plan, 'cash');

// Or using the service directly
$subscriptionService = app(\AMohamed\OfflineCashier\Services\SubscriptionService::class);
$subscription = $subscriptionService->create($user, $plan, 'cash');
```

### Subscription Operations

```php
// Cancel a subscription
$subscription->cancel(); // Will cancel at period end
$subscription->cancel(true); // Immediate cancellation

// Pause a subscription
$subscription->pause();

// Resume a subscription
$subscription->resume();

// Change plan
$newPlan = Plan::find(2);
$subscription->changePlan($newPlan);

// Check subscription status
if ($subscription->onTrial()) {
    // Subscription is on trial
}

if ($subscription->hasExpired()) {
    // Subscription has expired
}
```

### Checking Subscription Status

```php
// Check if user has an active subscription
if ($user->hasActiveSubscription()) {
    // User has an active subscription
}

// Get user's active subscription
$activeSubscription = $user->activeSubscription();

// Get all user's subscriptions
$allSubscriptions = $user->subscriptions;
```

## Handling Payments

### Creating Payments

```php
use AMohamed\OfflineCashier\Services\PaymentService;

$paymentService = app(PaymentService::class);

// Create an offline payment
$payment = $paymentService->createOfflinePayment(
    $subscription,
    99.99,
    'cash',
    'RECEIPT-123', // Reference number
    'Payment for annual subscription' // Notes
);

// Confirm the payment
$paymentService->confirmPayment($payment);
```

### Stripe Payments

```php
use AMohamed\OfflineCashier\Services\StripeService;

$stripeService = app(StripeService::class);

// Create a payment intent
$paymentIntent = $stripeService->createPaymentIntent($subscription);

// The client secret can be used with Stripe.js
$clientSecret = $paymentIntent['client_secret'];
```

## Working with Invoices

### Generating Invoices

```php
use AMohamed\OfflineCashier\Services\InvoiceService;

$invoiceService = app(InvoiceService::class);

// Generate an invoice for a payment
$invoice = $invoiceService->generate($payment);

// Generate PDF
$pdf = $invoiceService->generatePdf($invoice);
```

### Managing Invoices

```php
// Mark invoice as paid
$invoice->markAsPaid('PAYMENT-REF-123');

// Check invoice status
if ($invoice->isPaid()) {
    // Invoice is paid
}

if ($invoice->isPending()) {
    // Invoice is pending payment
}

// Get formatted total
$formattedTotal = $invoice->getFormattedTotalAttribute(); // Returns "$99.99"
```

## Events

The package dispatches various events that you can listen for:

```php
use AMohamed\OfflineCashier\Events\PaymentReceived;
use AMohamed\OfflineCashier\Events\SubscriptionCreated;
use AMohamed\OfflineCashier\Events\SubscriptionCanceled;

// In your EventServiceProvider
protected $listen = [
    PaymentReceived::class => [
        SendPaymentConfirmation::class,
    ],
    SubscriptionCreated::class => [
        SendWelcomeEmail::class,
    ],
    SubscriptionCanceled::class => [
        SendCancellationEmail::class,
    ],
];
```

## Notifications

The package includes built-in notifications:

```php
use AMohamed\OfflineCashier\Notifications\PaymentSuccessful;
use AMohamed\OfflineCashier\Notifications\SubscriptionCreated;

// Notifications are sent automatically when configured
// But you can also send them manually:
$user->notify(new PaymentSuccessful($payment));
$user->notify(new SubscriptionCreated($subscription));
```

## Facade Usage

The package provides a facade for common operations:

```php
use AMohamed\OfflineCashier\Facades\OfflineCashier;

// Create a subscription
$subscription = OfflineCashier::subscribe($user, $plan, 'cash');

// Create a payment
$payment = OfflineCashier::createPayment(
    $subscription,
    99.99,
    'cash',
    'RECEIPT-123'
);

// Confirm payment
OfflineCashier::confirmPayment($payment);
```

## Next Steps

- Learn about [Payment Methods](payment-methods.md)
- Set up [Stripe Integration](stripe-integration.md)
- Configure [Events & Notifications](events-notifications.md)
- Customize [Invoice Generation](invoice-generation.md) 