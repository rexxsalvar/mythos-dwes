<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Location> */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return ['name' => 'Local ficticio de pruebas', 'code' => fake()->unique()->uuid(), 'address' => 'Dirección ficticia', 'city' => 'Murcia', 'timezone' => 'Europe/Madrid', 'is_active' => true];
    }
}
