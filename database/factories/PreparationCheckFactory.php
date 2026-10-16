<?php

namespace Database\Factories;

use App\Models\GameSession;
use App\Models\PreparationCheck;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PreparationCheck> */
class PreparationCheckFactory extends Factory
{
    protected $model = PreparationCheck::class;

    public function definition(): array
    {
        return [
            'game_session_id' => GameSession::factory(), 'checked_by' => null,
            'label' => 'Revisar mecanismo de prueba', 'is_required' => true, 'passed' => null, 'checked_at' => null,
        ];
    }
}
