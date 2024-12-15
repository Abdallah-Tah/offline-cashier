<?php

namespace AMohamed\OfflineCashier;

use AMohamed\OfflineCashier\Contracts\PaymentManager;
use AMohamed\OfflineCashier\Contracts\SubscriptionManager;
use AMohamed\OfflineCashier\Models\Payment;
use AMohamed\OfflineCashier\Models\Plan;
use AMohamed\OfflineCashier\Models\Subscription;
use Illuminate\Database\Eloquent\Model;

class OfflineCashier
{
    public function __construct(
        protected SubscriptionManager $subscriptions,
        protected PaymentManager $payments
    ) {}

    public function subscribe(Model $user, Plan $plan, string $paymentMethod): Subscription
    {
        return $this->subscriptions->create($user, $plan, $paymentMethod);
    }

    public function createPayment(
        Subscription $subscription,
        float $amount,
        string $paymentMethod,
        ?string $referenceNumber = null
    ): Payment {
        return $this->payments->createOfflinePayment(
            $subscription,
            $amount,
            $paymentMethod,
            $referenceNumber
        );
    }

    public function confirmPayment(Payment $payment): bool
    {
        return $this->payments->confirmPayment($payment);
    }
} 