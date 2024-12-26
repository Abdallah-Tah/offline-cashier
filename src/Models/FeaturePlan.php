<?php

namespace AMohamed\OfflineCashier\Models;

use AMohamed\OfflineCashier\Models\Plan;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturePlan extends Pivot
{
    protected $fillable = ['feature_id', 'plan_id'];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}