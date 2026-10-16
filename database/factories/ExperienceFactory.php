<?php

namespace Database\Factories;

use App\Models\Experience;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Experience> */
class ExperienceFactory extends Factory
{
    protected $model = Experience::class;

    public function definition(): array
    {
        return [
            'name' => 'Experiencia de prueba '.fake()->unique()->numerify('####'),
            'slug' => fake()->unique()->uuid(), 'description' => 'Aventura ficticia para pruebas de Mythos.',
            'duration_minutes' => 75, 'min_players' => 2, 'max_players' => 6, 'is_active' => true,
        ];
    }
}
