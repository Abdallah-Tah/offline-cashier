<?php

use AMohamed\OfflineCashier\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

function createUser()
{
    return config('offline-cashier.models.user')::factory()->create();
}

function createPlan(array $attributes = [])
{
    return \AMohamed\OfflineCashier\Models\Plan::factory()->create($attributes);
}

function createSubscription(array $attributes = [])
{
    return \AMohamed\OfflineCashier\Models\Subscription::factory()->create($attributes);
}

function createPayment(array $attributes = [])
{
    return \AMohamed\OfflineCashier\Models\Payment::factory()->create($attributes);
} 