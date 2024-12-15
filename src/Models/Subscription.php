<?php

namespace AMohamed\OfflineCashier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'ends_at',
        'payment_method',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('offline-cashier.models.user'));
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(config('offline-cashier.models.plan'));
    }

    public function payments(): HasMany
    {
        return $this->hasMany(config('offline-cashier.models.payment'));
    }

    public function onTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at?->isFuture();
    }

    public function hasExpired(): bool
    {
        if ($this->status === 'trial' && $this->trial_ends_at?->isPast()) {
            return true;
        }

        return $this->ends_at?->isPast() ?? false;
    }

    public function cancel(bool $immediately = false): bool
    {
        return app(\AMohamed\OfflineCashier\Services\SubscriptionService::class)
            ->cancel($this, $immediately);
    }

    public function resume(): bool
    {
        return app(\AMohamed\OfflineCashier\Services\SubscriptionService::class)
            ->resume($this);
    }

    public function changePlan(\AMohamed\OfflineCashier\Models\Plan $newPlan): bool
    {
        return app(\AMohamed\OfflineCashier\Services\SubscriptionService::class)
            ->changePlan($this, $newPlan);
    }

    public function pause(): bool
    {
        return app(\AMohamed\OfflineCashier\Services\SubscriptionService::class)
            ->pause($this);
    }

    public function expire(): bool
    {
        return app(\AMohamed\OfflineCashier\Services\SubscriptionService::class)
            ->expire($this);
    }

    protected static function newFactory()
    {
        return \AMohamed\OfflineCashier\Database\Factories\SubscriptionFactory::new();
    }
} 