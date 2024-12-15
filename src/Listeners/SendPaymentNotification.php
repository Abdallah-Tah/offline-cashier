<?php

namespace AMohamed\OfflineCashier\Listeners;

use AMohamed\OfflineCashier\Events\PaymentReceived;
use AMohamed\OfflineCashier\Notifications\PaymentSuccessful;

class SendPaymentNotification
{
    public function handle(PaymentReceived $event): void
    {
        if (config('offline-cashier.notifications.payment_success')) {
            $event->payment->subscription->user->notify(
                new PaymentSuccessful($event->payment)
            );
        }
    }
} 