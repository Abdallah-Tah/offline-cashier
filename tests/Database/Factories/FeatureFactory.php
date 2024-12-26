<?php

namespace AMohamed\OfflineCashier\Tests\Database\Factories;

use AMohamed\OfflineCashier\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'is_active' => $this->faker->boolean,
        ];
    }
} 