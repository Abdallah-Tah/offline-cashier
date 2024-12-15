<?php

namespace AMohamed\OfflineCashier\Tests\Feature;

use AMohamed\OfflineCashier\Models\Plan;
use AMohamed\OfflineCashier\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_subscription(): void
    {
        $plan = Plan::factory()->create();
        $user = $this->createUser();

        $subscription = $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'active',
            'payment_method' => 'cash',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);
    }

    protected function createUser()
    {
        return config('offline-cashier.models.user')::factory()->create();
    }
} 