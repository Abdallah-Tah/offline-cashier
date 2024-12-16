# Events & Notifications

This guide covers the events and notifications system in the OfflineCashier package.

## Available Events

The package dispatches several events that you can listen for:

### Subscription Events

```php
use AMohamed\OfflineCashier\Events\SubscriptionCreated;
use AMohamed\OfflineCashier\Events\SubscriptionCanceled;
use AMohamed\OfflineCashier\Events\SubscriptionResumed;

// Fired when a new subscription is created
class SubscriptionCreated
{
    public function __construct(public Subscription $subscription)
    {
    }
}

// Fired when a subscription is canceled
class SubscriptionCanceled
{
    public function __construct(public Subscription $subscription)
    {
    }
}

// Fired when a subscription is resumed
class SubscriptionResumed
{
    public function __construct(public Subscription $subscription)
    {
    }
}
```

### Payment Events

```php
use AMohamed\OfflineCashier\Events\PaymentReceived;

// Fired when a payment is received and confirmed
class PaymentReceived
{
    public function __construct(public Payment $payment)
    {
    }
}
```

## Registering Event Listeners

Register your listeners in your application's `EventServiceProvider`:

```php
use App\Listeners\SendSubscriptionConfirmation;
use App\Listeners\NotifyAccountingDepartment;
use AMohamed\OfflineCashier\Events\PaymentReceived;
use AMohamed\OfflineCashier\Events\SubscriptionCreated;

protected $listen = [
    SubscriptionCreated::class => [
        SendSubscriptionConfirmation::class,
    ],
    PaymentReceived::class => [
        NotifyAccountingDepartment::class,
        SendPaymentReceipt::class,
    ],
];
```

## Creating Listeners

Example of a payment notification listener:

```php
namespace App\Listeners;

use AMohamed\OfflineCashier\Events\PaymentReceived;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPaymentReceipt implements ShouldQueue
{
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;
        $user = $payment->subscription->user;

        $user->notify(new PaymentReceiptNotification($payment));
    }
}
```

## Available Notifications

### Built-in Notifications

The package includes several built-in notifications:

```php
use AMohamed\OfflineCashier\Notifications\PaymentSuccessful;
use AMohamed\OfflineCashier\Notifications\SubscriptionCreated;

// Payment successful notification
class PaymentSuccessful extends Notification
{
    public function __construct(public Payment $payment)
    {
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

// Subscription created notification
class SubscriptionCreated extends Notification
{
    public function __construct(public Subscription $subscription)
    {
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
```

## Configuring Notifications

Configure notification settings in `config/offline-cashier.php`:

```php
'notifications' => [
    'channels' => ['mail', 'database'],
    'payment_success' => true,
    'payment_failed' => true,
    'subscription_created' => true,
    'subscription_canceled' => true,
    'subscription_expired' => true,
    'subscription_renewed' => true,
    'trial_ending' => true,
    'invoice_paid' => true,
],
```

## Custom Notifications

Create your own notifications by extending the base notification classes:

```php
namespace App\Notifications;

use AMohamed\OfflineCashier\Models\Payment;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomPaymentNotification extends Notification
{
    public function __construct(public Payment $payment)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database', 'slack'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Custom Payment Notification')
            ->markdown('notifications.payment', [
                'payment' => $this->payment,
                'user' => $notifiable,
            ]);
    }

    public function toDatabase($notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'status' => $this->payment->status,
        ];
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->success()
            ->content('New payment received: $' . $this->payment->amount);
    }
}
```

## Queuing Notifications

For better performance, you can queue notifications:

```php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // Customize the queue connection
    public $connection = 'redis';
    
    // Set queue priority
    public $queue = 'notifications';
    
    // Set delay
    public $delay = 60;
}
```

## Testing Events & Notifications

### Testing Events

```php
use AMohamed\OfflineCashier\Events\PaymentReceived;
use Illuminate\Support\Facades\Event;

public function test_it_dispatches_payment_received_event()
{
    Event::fake();

    $payment = createPayment();
    $paymentService->confirmPayment($payment);

    Event::assertDispatched(PaymentReceived::class, function ($event) use ($payment) {
        return $event->payment->id === $payment->id;
    });
}
```

### Testing Notifications

```php
use Illuminate\Support\Facades\Notification;
use AMohamed\OfflineCashier\Notifications\PaymentSuccessful;

public function test_it_sends_payment_notification()
{
    Notification::fake();

    $payment = createPayment();
    $user = $payment->subscription->user;

    $user->notify(new PaymentSuccessful($payment));

    Notification::assertSentTo(
        $user,
        PaymentSuccessful::class,
        function ($notification) use ($payment) {
            return $notification->payment->id === $payment->id;
        }
    );
}
```

## Next Steps

- Learn about [Invoice Generation](invoice-generation.md)
- Review [Testing](testing.md)
- Explore [Advanced Usage](advanced-usage.md) 