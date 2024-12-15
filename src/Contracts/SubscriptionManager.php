<?php

namespace AMohamed\OfflineCashier\Contracts;

use AMohamed\OfflineCashier\Models\Plan;
use AMohamed\OfflineCashier\Models\Subscription;
use Illuminate\Database\Eloquent\Model;

interface SubscriptionManager
{
    public function create(Model $user, Plan $plan, string $paymentMethod): Subscription;
    public function cancel(Subscription $subscription, bool $immediately = false): bool;
    public function resume(Subscription $subscription): bool;
    public function changePlan(Subscription $subscription, Plan $newPlan): bool;
    public function pause(Subscription $subscription): bool;
    public function expire(Subscription $subscription): bool;
} 