<?php

namespace Database\Factories;

use App\Models\Incident;
use App\Models\MaintenanceAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MaintenanceAction> */
class MaintenanceActionFactory extends Factory
{
    protected $model = MaintenanceAction::class;

    public function definition(): array
    {
        return [
            'incident_id' => Incident::factory(), 'technician_id' => User::factory(),
            'description' => 'Revisión ficticia del sensor de pruebas.', 'performed_at' => '2026-10-16 12:00:00',
        ];
    }
}
