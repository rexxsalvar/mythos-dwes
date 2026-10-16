<?php

namespace Database\Factories;

use App\Models\Experience;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Room> */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'experience_id' => Experience::factory(), 'name' => 'Sala de prueba '.fake()->unique()->uuid(),
            'location' => 'Ubicación ficticia de pruebas', 'capacity' => 6, 'status' => 'operational',
        ];
    }
}
