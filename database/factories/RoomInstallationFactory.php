<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomInstallation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RoomInstallation> */
class RoomInstallationFactory extends Factory
{
    protected $model = RoomInstallation::class;

    public function definition(): array
    {
        return ['room_id' => Room::factory(), 'experience_id' => fn (array $a) => Room::findOrFail($a['room_id'])->experience_id, 'installed_from' => '1970-01-01 00:00:00', 'installed_until' => null];
    }
}
