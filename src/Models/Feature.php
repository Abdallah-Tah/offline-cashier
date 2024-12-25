<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use AMohamed\OfflineCashier\Models\Plan;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class);
    }
}