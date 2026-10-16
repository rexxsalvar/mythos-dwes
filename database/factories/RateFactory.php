<?php

namespace Database\Factories;

use App\Models\Experience;
use App\Models\Rate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Rate> */
class RateFactory extends Factory
{
    protected $model = Rate::class;

    public function definition(): array
    {
        return [
            'experience_id' => Experience::factory(), 'players' => 2, 'total_cents' => 8000, 'currency' => 'EUR',
        ];
    }
}
