<?php

namespace Database\Factories;

use App\Models\GameSession;
use App\Models\Room;
use App\Models\RoomInstallation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GameSession> */
class GameSessionFactory extends Factory
{
    protected $model = GameSession::class;

    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'experience_id' => fn (array $attributes) => Room::findOrFail($attributes['room_id'])->experience_id,
            'game_master_id' => User::factory(),
            'starts_at' => $start = CarbonImmutable::parse('2026-10-17 10:00:00')->addDays(fake()->numberBetween(0, 365)),
            'ends_at' => $start->addMinutes(75), 'status' => 'scheduled', 'preparation_minutes' => null,
            'room_experience_id' => fn (array $a) => RoomInstallation::firstOrCreate(['room_id' => $a['room_id'], 'experience_id' => Room::findOrFail($a['room_id'])->experience_id, 'installed_from' => '1970-01-01 00:00:00'])->id,
        ];
    }
}
