<?php

namespace Database\Factories;

use App\Models\Experience;
use App\Models\Rate;
use App\Models\RatePlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Rate> */
class RateFactory extends Factory
{
    protected $model = Rate::class;

    public function definition(): array
    {
        return [
            'experience_id' => Experience::factory(), 'players' => 2, 'total_cents' => 8000, 'currency' => 'EUR',
            'rate_plan_id' => fn (array $a) => RatePlan::firstOrCreate(['experience_id' => $a['experience_id'], 'code' => 'factory-default'], ['name' => 'Plan de pruebas', 'currency' => $a['currency'], 'valid_from' => '1970-01-01 00:00:00', 'status' => 'active'])->id,
        ];
    }
}
