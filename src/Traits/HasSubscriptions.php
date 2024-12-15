<?php

namespace AMohamed\OfflineCashier\Traits;

use AMohamed\OfflineCashier\Models\Subscription;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSubscriptions
{
    public function subscriptions(): HasMany
    {
        return $this->hasMany(config('offline-cashier.models.subscription'));
    }

    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->orWhere(function ($query) {
                $query->where('status', 'trial')
                    ->where('trial_ends_at', '>', now());
            })
            ->first();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }
} 