<?php

namespace AMohamed\OfflineCashier\Events;

use AMohamed\OfflineCashier\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionCanceled
{
    use Dispatchable, SerializesModels;

    public function __construct(public Subscription $subscription)
    {
    }
} 