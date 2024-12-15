<?php

namespace AMohamed\OfflineCashier\Contracts;

use AMohamed\OfflineCashier\Models\Payment;
use AMohamed\OfflineCashier\Models\Subscription;

interface PaymentManager
{
    public function createOfflinePayment(
        Subscription $subscription,
        float $amount,
        string $paymentMethod,
        ?string $referenceNumber = null,
        ?string $notes = null
    ): Payment;

    public function confirmPayment(Payment $payment): bool;
    
    public function createStripePayment(
        Subscription $subscription,
        string $stripePaymentId,
        float $amount
    ): Payment;
} 