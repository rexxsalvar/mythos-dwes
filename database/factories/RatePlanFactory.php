<?php

namespace Database\Factories;

use App\Models\Experience;
use App\Models\RatePlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RatePlan> */
class RatePlanFactory extends Factory
{
    protected $model = RatePlan::class;

    public function definition(): array
    {
        return ['experience_id' => Experience::factory(), 'code' => fake()->unique()->uuid(), 'name' => 'Plan de prueba', 'currency' => 'EUR', 'valid_from' => '2026-01-01 00:00:00', 'valid_until' => null, 'status' => 'active'];
    }
}
