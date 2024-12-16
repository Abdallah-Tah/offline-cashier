# Stripe Integration

This guide covers how to integrate Stripe payments with the OfflineCashier package.

## Prerequisites

Before you begin, make sure you have:
1. A Stripe account
2. Stripe API keys
3. Stripe.js included in your frontend

## Configuration

### Environment Variables

Add your Stripe credentials to your `.env` file:

```env
STRIPE_KEY=your-stripe-publishable-key
STRIPE_SECRET=your-stripe-secret-key
STRIPE_WEBHOOK_SECRET=your-webhook-secret
```

### Package Configuration

Enable Stripe in your `config/offline-cashier.php`:

```php
'payment_methods' => [
    'stripe' => true,
],

'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook' => [
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
        'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],
],
```

## Backend Implementation

### Creating Payment Intents

```php
use AMohamed\OfflineCashier\Services\StripeService;

class SubscriptionController extends Controller
{
    public function createPaymentIntent(
        Request $request,
        StripeService $stripeService,
        Subscription $subscription
    ) {
        $paymentIntent = $stripeService->createPaymentIntent($subscription);

        return response()->json([
            'clientSecret' => $paymentIntent['client_secret'],
        ]);
    }
}
```

### Handling Webhooks

The package automatically handles Stripe webhooks. Configure your webhook endpoint in your Stripe dashboard:

```
https://your-domain.com/stripe/webhook
```

The webhook controller handles these events:

```php
protected $stripeEvents = [
    'payment_intent.succeeded',
    'payment_intent.failed',
    'subscription.created',
    'subscription.updated',
    'subscription.deleted',
];
```

## Frontend Implementation

### Including Stripe.js

```html
<script src="https://js.stripe.com/v3/"></script>
```

### Creating Payment Form

```html
<form id="payment-form">
    <div id="card-element">
        <!-- Stripe Elements will create form elements here -->
    </div>

    <div id="card-errors" role="alert"></div>

    <button type="submit">Pay Now</button>
</form>
```

### Implementing Payment Flow

```javascript
// Initialize Stripe
const stripe = Stripe('your-publishable-key');
const elements = stripe.elements();

// Create card element
const card = elements.create('card');
card.mount('#card-element');

// Handle form submission
const form = document.getElementById('payment-form');
form.addEventListener('submit', async (event) => {
    event.preventDefault();
    
    // Disable form while processing
    form.querySelector('button').disabled = true;
    
    try {
        // Get client secret from your backend
        const response = await fetch('/subscription/create-payment-intent', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                subscription_id: subscriptionId,
            }),
        });
        
        const { clientSecret } = await response.json();
        
        // Confirm payment with Stripe
        const { paymentIntent, error } = await stripe.confirmCardPayment(
            clientSecret,
            {
                payment_method: {
                    card: card,
                    billing_details: {
                        name: 'Customer Name',
                    },
                },
            }
        );
        
        if (error) {
            // Handle errors
            const errorElement = document.getElementById('card-errors');
            errorElement.textContent = error.message;
        } else {
            // Payment successful
            window.location.href = '/subscription/success';
        }
    } catch (e) {
        console.error('Error:', e);
    } finally {
        form.querySelector('button').disabled = false;
    }
});
```

## Webhook Security

The package automatically verifies webhook signatures. The webhook secret is configured in your `.env`:

```env
STRIPE_WEBHOOK_SECRET=your-webhook-secret
```

The webhook controller includes signature verification:

```php
protected function verifySignature(Request $request): void
{
    $signature = $request->header('Stripe-Signature');
    $secret = config('offline-cashier.stripe.webhook.secret');

    try {
        Webhook::constructEvent(
            $request->getContent(),
            $signature,
            $secret
        );
    } catch (\Exception $e) {
        throw new SignatureVerificationException('Invalid signature');
    }
}
```

## Testing Stripe Integration

### Test Mode

Always use test API keys in development:

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

### Test Cards

Use these test card numbers:
- `4242 4242 4242 4242` - Successful payment
- `4000 0000 0000 0002` - Declined payment
- `4000 0000 0000 3220` - 3D Secure authentication

### Testing Webhooks

Use the Stripe CLI for local webhook testing:

```bash
stripe listen --forward-to localhost:8000/stripe/webhook
```

### Feature Tests

The package includes test helpers for Stripe:

```php
use AMohamed\OfflineCashier\Tests\Feature\WebhookTest;

class StripeTest extends TestCase
{
    public function test_it_handles_successful_payment()
    {
        $subscription = $this->createSubscription();

        $event = $this->createStripeEvent('payment_intent.succeeded', [
            'id' => 'pi_123',
            'amount' => 9999,
            'metadata' => ['subscription_id' => $subscription->id],
        ]);

        $response = $this->postJson('/stripe/webhook', $event->toArray(), [
            'Stripe-Signature' => $this->generateSignature($event->toJSON()),
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('payments', [
            'subscription_id' => $subscription->id,
            'stripe_payment_id' => 'pi_123',
            'status' => 'completed',
        ]);
    }
}
```

## Error Handling

### Payment Failures

```php
try {
    $paymentIntent = $stripeService->createPaymentIntent($subscription);
} catch (\Stripe\Exception\CardException $e) {
    // Handle card errors
    return response()->json(['error' => $e->getMessage()], 422);
} catch (\Exception $e) {
    // Handle other errors
    return response()->json(['error' => 'Payment failed'], 500);
}
```

### Webhook Failures

```php
public function handleWebhook(Request $request): Response
{
    try {
        $this->verifySignature($request);
        $this->stripeService->handleWebhook($request->all());

        return response('Webhook handled', 200);
    } catch (SignatureVerificationException $e) {
        return response('Invalid signature', 400);
    } catch (\Exception $e) {
        return response('Webhook failed: ' . $e->getMessage(), 500);
    }
}
```

## Next Steps

- Configure [Events & Notifications](events-notifications.md)
- Learn about [Invoice Generation](invoice-generation.md)
- Review [Testing](testing.md) 