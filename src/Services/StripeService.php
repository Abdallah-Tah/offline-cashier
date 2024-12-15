<?php

namespace AMohamed\OfflineCashier\Services;

use AMohamed\OfflineCashier\Events\PaymentReceived;
use AMohamed\OfflineCashier\Models\Payment;
use AMohamed\OfflineCashier\Models\Subscription;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    protected StripeClient $stripeClient;

    public function __construct(protected PaymentService $payments)
    {
        $this->stripeClient = new StripeClient(config('offline-cashier.stripe.secret'));
    }

    public function handleWebhook(array $payload): void
    {
        $event = $this->stripeClient->events->retrieve($payload['id']);

        match ($event->type) {
            'payment_intent.succeeded' => $this->handleSuccessfulPayment($event->data->object),
            'payment_intent.failed' => $this->handleFailedPayment($event->data->object),
            default => null,
        };
    }

    protected function handleSuccessfulPayment(object $paymentIntent): void
    {
        $subscription = Subscription::where('stripe_id', $paymentIntent->metadata->subscription_id)->first();
        
        if (!$subscription) {
            return;
        }

        $payment = $this->payments->createStripePayment(
            $subscription,
            $paymentIntent->id,
            $paymentIntent->amount / 100
        );

        event(new PaymentReceived($payment));
    }

    protected function handleFailedPayment(object $paymentIntent): void
    {
        $subscription = Subscription::where('stripe_id', $paymentIntent->metadata->subscription_id)->first();
        
        if ($subscription) {
            $subscription->update(['status' => 'past_due']);
        }
    }

    public function createPaymentIntent(Subscription $subscription): array
    {
        try {
            $intent = $this->stripeClient->paymentIntents->create([
                'amount' => $subscription->plan->price * 100,
                'currency' => config('offline-cashier.currency', 'usd'),
                'metadata' => [
                    'subscription_id' => $subscription->id,
                ],
            ]);

            return [
                'client_secret' => $intent->client_secret,
                'public_key' => config('offline-cashier.stripe.key'),
            ];
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to create payment intent: ' . $e->getMessage());
        }
    }
} 