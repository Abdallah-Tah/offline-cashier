<?php

namespace AMohamed\OfflineCashier\Services;

use AMohamed\OfflineCashier\Contracts\SubscriptionManager;
use AMohamed\OfflineCashier\Events\SubscriptionCreated;
use AMohamed\OfflineCashier\Models\Plan;
use AMohamed\OfflineCashier\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SubscriptionService implements SubscriptionManager
{
    public function create(Model $user, Plan $plan, string $paymentMethod): Subscription
    {
        $subscription = new Subscription([
            'plan_id' => $plan->id,
            'status' => $plan->trial_period_days ? 'trial' : 'pending',
            'payment_method' => $paymentMethod,
            'trial_ends_at' => $plan->trial_period_days 
                ? Carbon::now()->addDays($plan->trial_period_days) 
                : null,
        ]);

        $subscription->user()->associate($user);
        $subscription->save();

        event(new SubscriptionCreated($subscription));

        return $subscription;
    }

    public function cancel(Subscription $subscription, bool $immediately = false): bool
    {
        $subscription->status = 'cancelled';
        
        if ($immediately) {
            $subscription->ends_at = Carbon::now();
        }

        return $subscription->save();
    }

    public function resume(Subscription $subscription): bool
    {
        if (!in_array($subscription->status, ['cancelled', 'paused'])) {
            return false;
        }

        $subscription->status = 'active';
        $subscription->ends_at = null;

        return $subscription->save();
    }

    public function changePlan(Subscription $subscription, Plan $newPlan): bool
    {
        $subscription->plan_id = $newPlan->id;
        return $subscription->save();
    }

    public function pause(Subscription $subscription): bool
    {
        $subscription->status = 'paused';
        return $subscription->save();
    }

    public function expire(Subscription $subscription): bool
    {
        $subscription->status = 'expired';
        $subscription->ends_at = now();
        return $subscription->save();
    }
} 