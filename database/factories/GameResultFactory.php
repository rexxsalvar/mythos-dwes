<?php

namespace Database\Factories;

use App\Models\GameResult;
use App\Models\GameSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GameResult> */
class GameResultFactory extends Factory
{
    protected $model = GameResult::class;

    public function definition(): array
    {
        return [
            'game_session_id' => GameSession::factory()->state(['status' => 'completed']),
            'recorded_by' => fn (array $attributes) => GameSession::findOrFail($attributes['game_session_id'])->game_master_id,
            'escaped' => true, 'elapsed_seconds' => fake()->numberBetween(1800, 4500), 'notes' => 'Resultado ficticio de prueba.',
        ];
    }
}
