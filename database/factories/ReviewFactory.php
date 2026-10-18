<?php

namespace Database\Factories;

use App\Models\GameSession;
use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Review> */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return ['reservation_id' => Reservation::factory()->for(GameSession::factory()->state(['status' => 'completed']))->state(['status' => 'completed']), 'user_id' => fn (array $a) => Reservation::findOrFail($a['reservation_id'])->user_id, 'rating' => 5, 'comment' => 'Valoración ficticia para demostrar el modelo.', 'status' => 'pending'];
    }
}
