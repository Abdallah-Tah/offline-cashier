<?php

namespace AMohamed\OfflineCashier\Models;

use Illuminate\Database\Eloquent\Model;
use AMohamed\OfflineCashier\Models\Plan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use AMohamed\OfflineCashier\Tests\Database\Factories\FeatureFactory;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'is_active'];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'feature_plan', 'feature_id', 'plan_id');
    }

    protected static function newFactory()
    {
        return FeatureFactory::new();
    }
}
