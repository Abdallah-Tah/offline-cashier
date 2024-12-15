<?php

namespace AMohamed\OfflineCashier\Database\Factories;

use AMohamed\OfflineCashier\Models\Plan;
use AMohamed\OfflineCashier\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'user_id' => config('offline-cashier.models.user')::factory(),
            'plan_id' => Plan::factory(),
            'status' => 'active',
            'payment_method' => $this->faker->randomElement(['cash', 'stripe', 'bank_transfer']),
            'trial_ends_at' => null,
            'ends_at' => null,
        ];
    }

    public function paused(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paused',
            ];
        });
    }

    public function cancelled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
                'ends_at' => now(),
            ];
        });
    }

    public function expired(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'expired',
                'ends_at' => now()->subDay(),
            ];
        });
    }

    public function trial(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(14),
            ];
        });
    }
} 