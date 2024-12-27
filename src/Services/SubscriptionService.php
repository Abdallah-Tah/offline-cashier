<?php

namespace AMohamed\OfflineCashier\Services;

use AMohamed\OfflineCashier\Contracts\SubscriptionManager;
use AMohamed\OfflineCashier\Events\SubscriptionCreated;
use AMohamed\OfflineCashier\Models\Plan;
use AMohamed\OfflineCashier\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Service class for managing subscriptions
 */
class SubscriptionService implements SubscriptionManager
{
    /**
     * Create a new subscription for a user
     *
     * @param Model $user The user model
     * @param Plan $plan The subscription plan
     * @param string $paymentMethod The payment method
     * @return Subscription The created subscription
     */
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

    /**
     * Cancel a subscription
     *
     * @param Subscription $subscription The subscription to cancel
     * @param bool $immediately Whether to cancel immediately or at period end
     * @return bool Success status
     */
    public function cancel(Subscription $subscription, bool $immediately = false): bool
    {
        $subscription->status = 'cancelled';

        if ($immediately) {
            $subscription->ends_at = Carbon::now();
        }

        return $subscription->save();
    }

    /**
     * Resume a cancelled or paused subscription
     *
     * @param Subscription $subscription The subscription to resume
     * @return bool Success status
     */
    public function resume(Subscription $subscription): bool
    {
        if (!in_array($subscription->status, ['cancelled', 'paused'])) {
            return false;
        }

        $subscription->status = 'active';
        $subscription->ends_at = null;

        return $subscription->save();
    }

    /**
     * Change subscription plan
     *
     * @param Subscription $subscription The subscription to modify
     * @param Plan $newPlan The new plan
     * @return bool Success status
     */
    public function changePlan(Subscription $subscription, Plan $newPlan): bool
    {
        $subscription->plan_id = $newPlan->id;
        return $subscription->save();
    }

    /**
     * Pause a subscription
     *
     * @param Subscription $subscription The subscription to pause
     * @return bool Success status
     */
    public function pause(Subscription $subscription): bool
    {
        $subscription->status = 'paused';
        return $subscription->save();
    }

    /**
     * Mark a subscription as expired
     *
     * @param Subscription $subscription The subscription to expire
     * @return bool Success status
     */
    public function expire(Subscription $subscription): bool
    {
        $subscription->status = 'expired';
        $subscription->ends_at = now();
        return $subscription->save();
    }

    /**
     * Customize subscription features
     * This method can be overridden to customize feature assignment
     *
     * @param Subscription $subscription The subscription
     * @param mixed $features The features to customize
     * @return void
     */
    public function customizeFeatures(Subscription $subscription, $features)
    {
        // This method can be left empty or repurposed
        // if customization is needed for specific business logic.
    }
}
