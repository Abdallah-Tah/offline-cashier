<?php

namespace AMohamed\OfflineCashier\Models;

use Illuminate\Database\Eloquent\Model;
use AMohamed\OfflineCashier\Models\Feature;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use AMohamed\OfflineCashier\Tests\Database\Factories\PlanFactory;

/**
 * Class Plan
 * 
 * Represents a subscription plan in the system.
 *
 * @property string $name The name of the plan
 * @property string $description A description of what the plan offers
 * @property float $price The price of the plan
 * @property string $billing_interval The billing interval ('month' or 'year')
 * @property int|null $trial_period_days Number of trial days, if applicable
 * @property string|null $stripe_price_id The Stripe Price ID for this plan
 */
class Plan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_interval',
        'trial_period_days',
        'stripe_price_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the subscriptions associated with this plan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(config('offline-cashier.models.subscription'));
    }

    /**
     * Get the features associated with this plan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(
            Feature::class,
            'feature_plan', 
            'plan_id',      
            'feature_id'   
        );
    }
    /**
     * Get the features associated with this plan.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeaturesAttribute()
    {
        return $this->features()->get();
    }

    /**
     * Get the duration of the plan in a human readable format.
     *
     * @return string Returns '1 year' for yearly plans, '1 month' for monthly plans, or 'N/A' otherwise
     */
    public function duration(): string
    {
        if ($this->billing_interval == 'year') {
            return '1 year';
        } elseif ($this->billing_interval == 'month') {
            return '1 month';
        }
        return 'N/A';
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \AMohamed\OfflineCashier\Tests\Database\Factories\PlanFactory
     */
    protected static function newFactory()
    {
        return PlanFactory::new();
    }
}
