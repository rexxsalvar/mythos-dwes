<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\GameResult;
use App\Models\GameSession;
use App\Models\Hint;
use App\Models\Incident;
use App\Models\MaintenanceAction;
use App\Models\PreparationCheck;
use App\Models\Rate;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MythosDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $users = [];
            foreach (['admin', 'recepcion', 'master', 'tecnico', 'cliente', 'miembro'] as $name) {
                $users[$name] = User::firstOrCreate(
                    ['email' => $name.'@example.test'],
                    ['name' => 'Demo '.ucfirst($name), 'password' => Hash::make('solo-pruebas-mythos'), 'email_verified_at' => '2026-10-01 10:00:00'],
                );
            }
            // Demo actor names do not implement the roles or permissions of sprint 3.
            $experience = Experience::updateOrCreate(['slug' => 'expedicion-maldita'], [
                'name' => 'Expedición Maldita', 'description' => 'Aventura de temática egipcia. Referencia del análisis de septiembre de 2026.',
                'duration_minutes' => 75, 'min_players' => 2, 'max_players' => 6, 'is_active' => true,
            ]);
            foreach ([2 => 8000, 3 => 9000, 4 => 10000, 5 => 11000, 6 => 13200] as $players => $cents) {
                Rate::updateOrCreate(['experience_id' => $experience->id, 'players' => $players], ['total_cents' => $cents, 'currency' => 'EUR']);
            }
            $room = Room::updateOrCreate(['name' => 'Sala demo Expedición Maldita'], [
                'experience_id' => $experience->id, 'location' => 'El Palmar, Murcia (referencia; sala de demostración)', 'capacity' => 6, 'status' => 'operational',
            ]);
            $maintenanceRoom = Room::updateOrCreate(['name' => 'Sala técnica ficticia'], [
                'experience_id' => $experience->id, 'location' => 'Ubicación ficticia de pruebas', 'capacity' => 6, 'status' => 'maintenance',
            ]);
            $team = Team::updateOrCreate(['owner_id' => $users['cliente']->id, 'name' => 'Los Escarabajos Demo']);
            $team->users()->syncWithoutDetaching([
                $users['cliente']->id => ['role' => 'captain', 'joined_at' => '2026-10-01 10:00:00'],
                $users['miembro']->id => ['role' => 'member', 'joined_at' => '2026-10-02 10:00:00'],
            ]);
            $sessions = [];
            foreach (['completed', 'confirmed', 'pending', 'cancelled', 'expired'] as $offset => $status) {
                $day = 15 + $offset;
                $session = GameSession::updateOrCreate([
                    'room_id' => $room->id, 'starts_at' => "2026-10-$day 17:00:00",
                ], [
                    'experience_id' => $experience->id, 'game_master_id' => $users['master']->id,
                    'ends_at' => "2026-10-$day 18:15:00", 'preparation_minutes' => null,
                    'status' => $status === 'completed' ? 'completed' : 'scheduled',
                ]);
                $sessions[$status] = $session;
                Reservation::updateOrCreate(['reference' => 'MYTHOS-DEMO-'.strtoupper($status)], [
                    'user_id' => $users['cliente']->id, 'game_session_id' => $session->id, 'team_id' => $team->id,
                    'players' => 4, 'total_cents' => 10000, 'currency' => 'EUR', 'status' => $status,
                    'confirmed_at' => in_array($status, ['confirmed', 'completed'], true) ? '2026-10-10 12:00:00' : null,
                    'cancelled_at' => $status === 'cancelled' ? '2026-10-11 12:00:00' : null,
                    'expires_at' => null, 'needs_review' => false,
                ]);
            }
            $hint = Hint::updateOrCreate(['experience_id' => $experience->id, 'code' => 'DEMO-01'], ['description' => 'Pista ficticia: revisar los símbolos del panel.']);
            Hint::updateOrCreate(['experience_id' => $experience->id, 'code' => 'DEMO-02'], ['description' => 'Pista ficticia: comparar las figuras de prueba.']);
            $completed = $sessions['completed'];
            $completed->hints()->syncWithoutDetaching([$hint->id => ['used_at' => '2026-10-15 17:30:00', 'penalty_seconds' => null]]);
            foreach (['Revisar mecanismos', 'Comprobar iluminación', 'Preparar material'] as $label) {
                PreparationCheck::updateOrCreate(['game_session_id' => $completed->id, 'label' => $label], [
                    'checked_by' => $users['master']->id, 'is_required' => true, 'passed' => true, 'checked_at' => '2026-10-15 16:50:00',
                ]);
            }
            GameResult::updateOrCreate(['game_session_id' => $completed->id], [
                'recorded_by' => $users['master']->id, 'escaped' => true, 'elapsed_seconds' => 4200, 'notes' => 'Partida ficticia para demostrar relaciones.',
            ]);
            $resolved = Incident::updateOrCreate(['room_id' => $room->id, 'title' => 'Sensor demo revisado'], [
                'game_session_id' => $completed->id, 'reported_by' => $users['master']->id, 'assigned_to' => $users['tecnico']->id,
                'description' => 'Incidencia ficticia ya resuelta.', 'severity' => 'medium', 'status' => 'resolved', 'resolved_at' => '2026-10-15 20:00:00',
            ]);
            Incident::updateOrCreate(['room_id' => $maintenanceRoom->id, 'title' => 'Bloqueo técnico ficticio'], [
                'game_session_id' => null, 'reported_by' => $users['recepcion']->id, 'assigned_to' => $users['tecnico']->id,
                'description' => 'Avería crítica ficticia en sala sin sesiones reservables.', 'severity' => 'critical', 'status' => 'open', 'resolved_at' => null,
            ]);
            MaintenanceAction::updateOrCreate(['incident_id' => $resolved->id, 'performed_at' => '2026-10-15 19:45:00'], [
                'technician_id' => $users['tecnico']->id, 'description' => 'Sustitución simulada del sensor y comprobación funcional.',
            ]);
        });
    }
}
