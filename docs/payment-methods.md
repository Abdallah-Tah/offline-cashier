# Payment Methods

This guide covers all payment methods supported by the OfflineCashier package and how to implement them.

## Supported Payment Methods

The package supports the following payment methods out of the box:
- Cash
- Check
- Bank Transfer
- Stripe (online payments)

## Configuration

Enable or disable payment methods in your `config/offline-cashier.php`:

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

## Offline Payments

### Cash Payments

```php
use AMohamed\OfflineCashier\Services\PaymentService;

$paymentService = app(PaymentService::class);

// Create a cash payment
$payment = $paymentService->createOfflinePayment(
    $subscription,
    99.99,
    'cash',
    'CASH-123', // Receipt number
    'Cash payment received at office' // Optional notes
);

// Confirm the payment
$paymentService->confirmPayment($payment);
```

### Check Payments

```php
// Create a check payment
$payment = $paymentService->createOfflinePayment(
    $subscription,
    99.99,
    'check',
    'CHECK-456', // Check number
    'Check received on 2024-01-15'
);

// Confirm after check clears
$paymentService->confirmPayment($payment);
```

### Bank Transfer

```php
// Create a bank transfer payment
$payment = $paymentService->createOfflinePayment(
    $subscription,
    99.99,
    'bank_transfer',
    'TRANSFER-789', // Transfer reference
    'Bank transfer received'
);

// Confirm after verifying transfer
$paymentService->confirmPayment($payment);
```

## Online Payments (Stripe)

### Configuration

First, set up your Stripe credentials in `.env`:

```env
STRIPE_KEY=your-stripe-publishable-key
STRIPE_SECRET=your-stripe-secret-key
STRIPE_WEBHOOK_SECRET=your-webhook-secret
```

### Creating Stripe Payments

```php
use AMohamed\OfflineCashier\Services\StripeService;

$stripeService = app(StripeService::class);

// Create a payment intent
$paymentIntent = $stripeService->createPaymentIntent($subscription);

// Use the client secret with Stripe.js
$clientSecret = $paymentIntent['client_secret'];
```

### Frontend Integration

```javascript
// Using Stripe.js
const stripe = Stripe('your-publishable-key');
const elements = stripe.elements();

// Create payment form
const card = elements.create('card');
card.mount('#card-element');

// Handle form submission
const form = document.getElementById('payment-form');
form.addEventListener('submit', async (event) => {
    event.preventDefault();
    
    const { paymentIntent, error } = await stripe.confirmCardPayment(
        clientSecret,
        {
            payment_method: {
                card: card,
            }
        }
    );

    if (error) {
        // Handle error
    } else {
        // Payment successful
    }
});
```

### Webhook Handling

The package automatically handles Stripe webhooks. Configure your webhook endpoint in your Stripe dashboard:

```
https://your-domain.com/stripe/webhook
```

The webhook controller handles various Stripe events:
- `payment_intent.succeeded`
- `payment_intent.failed`
- `subscription.created`
- `subscription.updated`
- `subscription.deleted`

## Custom Payment Methods

You can add custom payment methods by extending the PaymentService:

```php
use AMohamed\OfflineCashier\Services\PaymentService;

class CustomPaymentService extends PaymentService
{
    public function createCustomPayment(
        Subscription $subscription,
        float $amount,
        string $reference
    ): Payment {
        return $this->createOfflinePayment(
            $subscription,
            $amount,
            'custom_method',
            $reference
        );
    }
}
```

Then bind your service in a service provider:

```php
$this->app->bind(
    \AMohamed\OfflineCashier\Contracts\PaymentManager::class,
    CustomPaymentService::class
);
```

## Payment Validation

The package includes validation for payment references:

```php
use AMohamed\OfflineCashier\Http\Requests\CreatePaymentRequest;

class PaymentController extends Controller
{
    public function store(CreatePaymentRequest $request)
    {
        // Request is automatically validated
        $payment = $paymentService->createOfflinePayment(
            $subscription,
            $request->amount,
            $request->payment_method,
            $request->reference_number
        );
    }
}
```

## Payment Events

The package dispatches events for payment operations:

```php
use AMohamed\OfflineCashier\Events\PaymentReceived;

// Listen for payment events
Event::listen(function (PaymentReceived $event) {
    $payment = $event->payment;
    // Handle successful payment
});
```

## Next Steps

- Set up [Stripe Integration](stripe-integration.md)
- Configure [Events & Notifications](events-notifications.md)
- Learn about [Invoice Generation](invoice-generation.md) 