<?php

namespace AMohamed\OfflineCashier\Tests\Feature;

use AMohamed\OfflineCashier\Events\PaymentReceived;
use AMohamed\OfflineCashier\Models\Payment;
use AMohamed\OfflineCashier\Models\Plan;
use AMohamed\OfflineCashier\Models\Subscription;
use AMohamed\OfflineCashier\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_offline_payment(): void
    {
        $subscription = $this->createSubscription();

        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'amount' => 99.99,
            'payment_method' => 'cash',
            'status' => 'pending',
            'reference_number' => 'CASH-123',
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'subscription_id' => $subscription->id,
            'amount' => 99.99,
            'payment_method' => 'cash',
        ]);
    }

    public function test_it_fires_event_when_payment_confirmed(): void
    {
        Event::fake();

        $payment = Payment::create([
            'subscription_id' => $this->createSubscription()->id,
            'amount' => 99.99,
            'payment_method' => 'cash',
            'status' => 'pending',
        ]);

        $payment->update(['status' => 'completed']);

        Event::assertDispatched(PaymentReceived::class, function ($event) use ($payment) {
            return $event->payment->id === $payment->id;
        });
    }

    protected function createSubscription(): Subscription
    {
        $plan = Plan::factory()->create();
        $user = $this->createUser();

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'payment_method' => 'cash',
        ]);
    }

    protected function createUser()
    {
        return config('offline-cashier.models.user')::factory()->create();
    }
} 