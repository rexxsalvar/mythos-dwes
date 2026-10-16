<?php

namespace Database\Factories;

use App\Models\Incident;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Incident> */
class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    public function definition(): array
    {
        return [
            'room_id' => Room::factory(), 'game_session_id' => null,
            'reported_by' => User::factory(), 'assigned_to' => null,
            'title' => 'Revisar sensor de prueba', 'description' => 'Incidencia ficticia para comprobar el flujo de mantenimiento.',
            'severity' => 'medium', 'status' => 'open', 'resolved_at' => null,
        ];
    }
}
