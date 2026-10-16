<?php

namespace Database\Factories;

use App\Models\Experience;
use App\Models\Hint;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Hint> */
class HintFactory extends Factory
{
    protected $model = Hint::class;

    public function definition(): array
    {
        return [
            'experience_id' => Experience::factory(), 'code' => fake()->unique()->bothify('PISTA-####??'),
            'description' => 'Pista ficticia: observar los símbolos del panel de pruebas.',
        ];
    }
}
