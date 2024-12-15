<?php

namespace AMohamed\OfflineCashier\Services;

use AMohamed\OfflineCashier\Models\Payment;
use AMohamed\OfflineCashier\Models\Subscription;
use Carbon\Carbon;
use AMohamed\OfflineCashier\Contracts\PaymentManager;
use AMohamed\OfflineCashier\Events\PaymentReceived;

class PaymentService implements PaymentManager
{
    public function __construct(protected InvoiceService $invoices)
    {}

    public function createOfflinePayment(
        Subscription $subscription,
        float $amount,
        string $paymentMethod,
        string $referenceNumber = null,
        string $notes = null
    ): Payment {
        $payment = $subscription->payments()->create([
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'status' => 'pending',
            'reference_number' => $referenceNumber,
            'notes' => $notes,
        ]);

        return $payment;
    }

    public function confirmPayment(Payment $payment): bool
    {
        $payment->status = 'completed';
        $payment->paid_at = Carbon::now();
        
        if ($payment->save()) {
            $this->updateSubscriptionStatus($payment->subscription);
            $this->invoices->generate($payment);
            event(new PaymentReceived($payment));
            return true;
        }

        return false;
    }

    public function createStripePayment(
        Subscription $subscription,
        string $stripePaymentId,
        float $amount
    ): Payment {
        return $subscription->payments()->create([
            'amount' => $amount,
            'payment_method' => 'stripe',
            'status' => 'completed',
            'stripe_payment_id' => $stripePaymentId,
            'paid_at' => Carbon::now(),
        ]);
    }

    protected function updateSubscriptionStatus(Subscription $subscription): void
    {
        if ($subscription->status === 'pending' || $subscription->status === 'trial') {
            $subscription->status = 'active';
            $subscription->save();
        }
    }
} 