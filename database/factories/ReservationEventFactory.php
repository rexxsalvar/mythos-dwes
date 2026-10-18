<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\ReservationEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ReservationEvent> */
class ReservationEventFactory extends Factory
{
    protected $model = ReservationEvent::class;

    public function definition(): array
    {
        return ['reservation_id' => Reservation::factory(), 'actor_id' => User::factory(), 'action' => 'created', 'occurred_at' => '2026-10-10 10:00:00', 'after_values' => ['status' => 'pending']];
    }
}
