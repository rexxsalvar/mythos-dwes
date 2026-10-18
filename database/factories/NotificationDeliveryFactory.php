<?php

namespace Database\Factories;

use App\Models\NotificationDelivery;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NotificationDelivery> */
class NotificationDeliveryFactory extends Factory
{
    protected $model = NotificationDelivery::class;

    public function definition(): array
    {
        return ['idempotency_key' => fake()->unique()->uuid(), 'user_id' => null, 'reservation_id' => Reservation::factory(), 'purpose' => 'reservation_confirmation', 'channel' => 'email', 'recipient' => 'cliente@example.test', 'locale' => 'es', 'status' => 'scheduled', 'scheduled_for' => '2026-10-16 12:00:00', 'attempts' => 0];
    }
}
