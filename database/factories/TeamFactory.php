<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Team> */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'owner_id' => User::factory(), 'name' => 'Equipo de prueba '.fake()->unique()->numerify('####'),
        ];
    }
}
