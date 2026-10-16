<?php

namespace Database\Factories;

use App\Models\GameSession;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Reservation> */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'reference' => fake()->unique()->uuid(), 'user_id' => User::factory(),
            'game_session_id' => GameSession::factory(), 'team_id' => null,
            'players' => 2, 'total_cents' => 8000, 'currency' => 'EUR', 'status' => 'pending',
            'expires_at' => null, 'confirmed_at' => null, 'cancelled_at' => null, 'needs_review' => false,
        ];
    }
}
