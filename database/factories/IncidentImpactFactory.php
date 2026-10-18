<?php

namespace Database\Factories;

use App\Models\Incident;
use App\Models\IncidentImpact;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<IncidentImpact> */
class IncidentImpactFactory extends Factory
{
    protected $model = IncidentImpact::class;

    public function definition(): array
    {
        return ['reservation_id' => Reservation::factory(), 'incident_id' => fn (array $a) => Incident::factory()->create(['room_id' => Reservation::findOrFail($a['reservation_id'])->gameSession->room_id])->id, 'detected_at' => '2026-10-16 10:00:00', 'status' => 'pending'];
    }
}
