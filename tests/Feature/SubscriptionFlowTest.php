<?php

use Illuminate\Support\Facades\Event;
use AMohamed\OfflineCashier\Models\Invoice;
use AMohamed\OfflineCashier\Events\PaymentReceived;
use AMohamed\OfflineCashier\Services\PaymentService;
use AMohamed\OfflineCashier\Events\SubscriptionCreated;
use AMohamed\OfflineCashier\Services\SubscriptionService;

test('complete subscription flow', function () {
    Event::fake();
    
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

    Event::assertDispatched(SubscriptionCreated::class);

    // 4. Create and confirm payment
    $paymentService = app(PaymentService::class);
    $payment = $paymentService->createOfflinePayment($subscription, $plan->price, 'cash', 'CASH-123');

    $paymentService->confirmPayment($payment);

    Event::assertDispatched(PaymentReceived::class);

    // 5. Verify subscription is active
    $subscription->refresh();
    expect($subscription->status)->toBe('active');

    // 6. Verify invoice was generated
    expect(Invoice::where([
        'payment_id' => $payment->id,
        'total' => $plan->price,
        'status' => 'paid',
    ])->exists())->toBeTrue();

    // 7. Cancel subscription
    $subscription->cancel();
    expect($subscription->status)->toBe('cancelled');

    // 8. Resume subscription
    $subscription->resume();
    expect($subscription->status)->toBe('active');

    // 9. Change plan
    $newPlan = createPlan(['price' => 199.99]);
    $subscription->changePlan($newPlan);
    expect($subscription->plan_id)->toBe($newPlan->id);
});

test('trial subscription flow', function () {
    // Create a plan with trial
    $plan = createPlan(['trial_period_days' => 14]);
    $user = createUser();

    // Create subscription with trial
    $subscriptionService = app(SubscriptionService::class);
    $subscription = $subscriptionService->create($user, $plan, 'cash');

    expect($subscription->onTrial())->toBeTrue()
        ->and($subscription->hasExpired())->toBeFalse();
    
    // Fast forward to trial end
    test()->travel(15)->days();
    
    expect($subscription->onTrial())->toBeFalse()
        ->and($subscription->hasExpired())->toBeTrue();
});

test('subscription can be paused', function () {
    $subscription = createSubscription();
    $subscription->pause();
    expect($subscription->status)->toBe('paused');
});

test('subscription can be resumed', function () {
    $subscription = createSubscription(['status' => 'paused']);
    $subscription->resume();
    expect($subscription->status)->toBe('active');
});

test('subscription can be changed', function () {
    $subscription = createSubscription();
    $newPlan = createPlan();
    $subscription->changePlan($newPlan);
    expect($subscription->plan_id)->toBe($newPlan->id);
});

test('subscription can be cancelled', function () {
    $subscription = createSubscription();
    $subscription->cancel();
    expect($subscription->status)->toBe('cancelled');
});

test('subscription can be expired', function () {
    $subscription = createSubscription();
    $subscription->expire();
    expect($subscription->status)->toBe('expired');
});
