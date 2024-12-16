# Testing

This guide covers how to test your implementation of the OfflineCashier package.

## Setup

### Configuration

First, configure your testing environment in `.env.testing`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:

STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_test_...
```

### Test Case

The package includes a base TestCase class that sets up the testing environment:

```php
namespace AMohamed\OfflineCashier\Tests;

use AMohamed\OfflineCashier\OfflineCashierServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => [
                __DIR__ . '/Database/Migrations',
                __DIR__ . '/../database/migrations',
            ],
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            OfflineCashierServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
```

## Test Helpers

### Factory Helpers

The package provides several factory helpers in `tests/Pest.php`:

```php
function createUser()
{
    return config('offline-cashier.models.user')::factory()->create();
}

function createPlan(array $attributes = [])
{
    return \AMohamed\OfflineCashier\Models\Plan::factory()->create($attributes);
}

function createSubscription(array $attributes = [])
{
    return \AMohamed\OfflineCashier\Models\Subscription::factory()->create($attributes);
}

function createPayment(array $attributes = [])
{
    return \AMohamed\OfflineCashier\Models\Payment::factory()->create($attributes);
}
```

### Model Factories

#### Plan Factory

```php
namespace AMohamed\OfflineCashier\Database\Factories;

class PlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'billing_interval' => $this->faker->randomElement(['month', 'year']),
            'trial_period_days' => $this->faker->optional()->numberBetween(7, 30),
            'features' => ['feature1', 'feature2', 'feature3'],
        ];
    }

    public function monthly(float $price = 9.99): self
    {
        return $this->state(['billing_interval' => 'month', 'price' => $price]);
    }

    public function yearly(float $price = 99.99): self
    {
        return $this->state(['billing_interval' => 'year', 'price' => $price]);
    }
}
```

#### Subscription Factory

```php
namespace AMohamed\OfflineCashier\Database\Factories;

class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => config('offline-cashier.models.user')::factory(),
            'plan_id' => Plan::factory(),
            'status' => 'active',
            'payment_method' => $this->faker->randomElement(['cash', 'stripe']),
            'trial_ends_at' => null,
            'ends_at' => null,
        ];
    }

    public function trial(): self
    {
        return $this->state([
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    public function cancelled(): self
    {
        return $this->state([
            'status' => 'cancelled',
            'ends_at' => now(),
        ]);
    }
}
```

## Feature Tests

### Testing Subscriptions

```php
use AMohamed\OfflineCashier\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

test('complete subscription flow', function () {
    // 1. Create a plan
    $plan = createPlan([
        'name' => 'Premium Plan',
        'price' => 99.99,
        'billing_interval' => 'month',
    ]);

    // 2. Create a user
    $user = createUser();

    // 3. Create a subscription
    $subscriptionService = app(SubscriptionService::class);
    $subscription = $subscriptionService->create($user, $plan, 'cash');

    expect($subscription->status)->toBe('pending');

    // 4. Create and confirm payment
    $paymentService = app(PaymentService::class);
    $payment = $paymentService->createOfflinePayment(
        $subscription,
        $plan->price,
        'cash',
        'CASH-123'
    );

    $paymentService->confirmPayment($payment);

    // 5. Verify subscription is active
    $subscription->refresh();
    expect($subscription->status)->toBe('active');

    // 6. Verify invoice was generated
    expect(Invoice::where([
        'payment_id' => $payment->id,
        'total' => $plan->price,
        'status' => 'paid',
    ])->exists())->toBeTrue();
});
```

### Testing Payments

```php
test('it can process offline payments', function () {
    $subscription = createSubscription();
    
    $payment = createPayment([
        'subscription_id' => $subscription->id,
        'amount' => 99.99,
        'payment_method' => 'cash',
        'status' => 'pending',
    ]);

    $paymentService = app(PaymentService::class);
    $paymentService->confirmPayment($payment);

    $payment->refresh();
    expect($payment->status)->toBe('completed')
        ->and($payment->paid_at)->not->toBeNull();
});
```

### Testing Webhooks

```php
test('it handles stripe webhooks', function () {
    $subscription = createSubscription();

    $event = createStripeEvent('payment_intent.succeeded', [
        'id' => 'pi_123',
        'amount' => 9999,
        'metadata' => ['subscription_id' => $subscription->id],
    ]);

    $response = $this->postJson('/stripe/webhook', $event->toArray(), [
        'Stripe-Signature' => generateSignature($event->toJSON()),
    ]);

    $response->assertStatus(200);
    
    expect(Payment::where([
        'subscription_id' => $subscription->id,
        'stripe_payment_id' => 'pi_123',
        'status' => 'completed',
    ])->exists())->toBeTrue();
});
```

## Testing Events

```php
test('it dispatches events', function () {
    Event::fake();

    $subscription = createSubscription();
    $payment = createPayment(['subscription_id' => $subscription->id]);
    
    $paymentService = app(PaymentService::class);
    $paymentService->confirmPayment($payment);

    Event::assertDispatched(PaymentReceived::class);
});
```

## Testing Notifications

```php
test('it sends notifications', function () {
    Notification::fake();

    $payment = createPayment();
    $user = $payment->subscription->user;

    $paymentService = app(PaymentService::class);
    $paymentService->confirmPayment($payment);

    Notification::assertSentTo(
        $user,
        PaymentSuccessful::class,
        fn ($notification) => $notification->payment->id === $payment->id
    );
});
```

## Running Tests

### Running All Tests

```bash
composer test
```

### Running Specific Tests

```bash
# Run a specific test file
./vendor/bin/pest tests/Feature/SubscriptionTest.php

# Run tests with coverage
composer test-coverage

# Run tests matching a filter
./vendor/bin/pest --filter="test_it_can_create_subscription"
```

## Next Steps

- Review [Advanced Usage](advanced-usage.md)
- Learn about [Contributing](contributing.md)
- Check [Troubleshooting](troubleshooting.md) 