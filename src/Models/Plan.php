<?php

namespace AMohamed\OfflineCashier\Models;

use Illuminate\Database\Eloquent\Model;
use AMohamed\OfflineCashier\Models\Feature;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use AMohamed\OfflineCashier\Tests\Database\Factories\PlanFactory;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_interval',
        'trial_period_days',
        'stripe_price_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(config('offline-cashier.models.subscription'));
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(
            Feature::class,
            'feature_plan', // Pivot table
            'plan_id',      // Foreign key for the plan
            'feature_id'    // Foreign key for the feature
        );
    }

    protected static function newFactory()
    {
        return PlanFactory::new();
    }
}
