<?php

namespace AMohamed\OfflineCashier\Tests\Feature;

use AMohamed\OfflineCashier\Models\Subscription;
use AMohamed\OfflineCashier\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\Event;
use Stripe\PaymentIntent;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_handles_successful_payment(): void
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

    protected function createStripeEvent(string $type, array $data): Event
    {
        return new Event([
            'id' => 'evt_123',
            'type' => $type,
            'data' => ['object' => new PaymentIntent($data)],
        ]);
    }

    protected function generateSignature(string $payload): string
    {
        $timestamp = time();
        $secret = config('offline-cashier.stripe.webhook.secret');

        return $timestamp . '.' . hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
    }
} 