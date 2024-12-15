<?php

namespace AMohamed\OfflineCashier\Notifications;

use AMohamed\OfflineCashier\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionCreated extends Notification
{
    use Queueable;

    public function __construct(public Subscription $subscription)
    {
    }

    public function via($notifiable): array
    {
        return config('offline-cashier.notifications.channels', ['mail']);
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Subscription Created')
            ->line('Your subscription has been created successfully.')
            ->line('Plan: ' . $this->subscription->plan->name)
            ->line('Status: ' . $this->subscription->status)
            ->line('Thank you for subscribing!');
    }
} 