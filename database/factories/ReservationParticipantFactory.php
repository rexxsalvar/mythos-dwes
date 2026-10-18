<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\ReservationParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ReservationParticipant> */
class ReservationParticipantFactory extends Factory
{
    protected $model = ReservationParticipant::class;

    public function definition(): array
    {
        return ['reservation_id' => Reservation::factory(), 'user_id' => null, 'position' => 1, 'display_name' => 'Participante ficticio'];
    }
}
