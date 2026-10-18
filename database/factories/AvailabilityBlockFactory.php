<?php

namespace Database\Factories;

use App\Models\AvailabilityBlock;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AvailabilityBlock> */
class AvailabilityBlockFactory extends Factory
{
    protected $model = AvailabilityBlock::class;

    public function definition(): array
    {
        return ['room_id' => Room::factory(), 'user_id' => null, 'incident_id' => null, 'created_by' => User::factory(), 'starts_at' => '2026-10-20 10:00:00', 'ends_at' => '2026-10-20 11:00:00', 'reason' => 'Mantenimiento de demostración', 'kind' => 'maintenance'];
    }
}
