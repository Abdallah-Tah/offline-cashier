<?php

use AMohamed\OfflineCashier\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

function createUser()
{
    return config('offline-cashier.models.user')::factory()->create();
}

function createPlan(array $attributes = [])
{
    $plan = \AMohamed\OfflineCashier\Models\Plan::factory()->create($attributes);

    // Create and attach features
    $features = \AMohamed\OfflineCashier\Models\Feature::factory()->count(3)->create();
    $plan->features()->attach($features->pluck('id'));

    // Eager-load the features relationship
    return $plan->load('features');
}



function createSubscription(array $attributes = [])
{
    return \AMohamed\OfflineCashier\Models\Subscription::factory()->create($attributes);
}

function createPayment(array $attributes = [])
{
    return \AMohamed\OfflineCashier\Models\Payment::factory()->create($attributes);
}

function createFeature(array $attributes = [], $count = 1)
{
    return \AMohamed\OfflineCashier\Models\Feature::factory()->count($count)->create($attributes);
}

function createFeaturePlan(array $attributes = [])
{
    return \AMohamed\OfflineCashier\Models\FeaturePlan::factory()->create($attributes);
}
