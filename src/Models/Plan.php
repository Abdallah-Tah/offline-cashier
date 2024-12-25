<?php

namespace AMohamed\OfflineCashier\Models;

use App\Models\Feature;
use Illuminate\Database\Eloquent\Model;
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
        'features',
        'stripe_price_id',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(config('offline-cashier.models.subscription'));
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class);
    }

    protected static function newFactory()
    {
        return PlanFactory::new();
    }
} 