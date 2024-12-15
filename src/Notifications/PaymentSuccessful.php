<?php

namespace AMohamed\OfflineCashier\Notifications;

use AMohamed\OfflineCashier\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessful extends Notification
{
    use Queueable;

    public function __construct(public Payment $payment)
    {
    }

    public function via($notifiable): array
    {
        return config('offline-cashier.notifications.channels', ['mail']);
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Received')
            ->line('We have received your payment of ' . $this->payment->amount)
            ->line('Payment Method: ' . $this->payment->payment_method)
            ->line('Reference Number: ' . $this->payment->reference_number)
            ->line('Thank you for your business!');
    }
} 