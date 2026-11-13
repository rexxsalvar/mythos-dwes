<?php

use App\Models\Experience;
use App\Models\GameSession;
use App\Models\Incident;
use App\Models\MaintenanceAction;
use App\Models\Rate;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessRolesSeeder;

beforeEach(function () {
    $this->seed(AccessRolesSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-11-13 10:00:00', 'UTC'));
    $this->admin = User::factory()->create()->assignRole('administrador');
});

test('catalogue writes validate authorize and archive instead of removing history', function () {
    $data = ['name' => 'Prueba', 'slug' => 'prueba', 'description' => 'Juego de prueba', 'duration_minutes' => 60, 'min_players' => 2, 'max_players' => 6];
    $this->post(route('administration.experiences.store'), $data)->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create()->assignRole('cliente'))->post(route('administration.experiences.store'), $data)->assertForbidden();
    $this->actingAs($this->admin)->post(route('administration.experiences.store'), $data)->assertRedirect();
    $experience = Experience::where('slug', 'prueba')->sole();
    $this->get(route('administration.experiences.edit', $experience))->assertOk()->assertSee('Editar experiencia');
    $this->patch(route('administration.experiences.update', $experience), ['max_players' => 1] + $data)->assertSessionHasErrors('max_players');
    $this->patch(route('administration.experiences.update', $experience), ['name' => 'Prueba editada'] + $data)->assertRedirect();
    $this->get(route('experiences.show', 'prueba'))->assertOk()->assertSee('Prueba editada');
    $this->delete(route('administration.experiences.destroy', $experience))->assertRedirect();
    expect($experience->fresh()->archived_at)->not->toBeNull();
    $this->get(route('experiences.index'))->assertDontSee('Prueba editada');
    $this->actingAs(User::factory()->create()->assignRole('cliente'))->get(route('experiences.show', 'prueba'))->assertForbidden();
});

test('a room and session can be created edited and cancelled through validated web forms', function () {
    $experience = Experience::factory()->create();
    $master = User::factory()->create()->assignRole('game_master');
    $data = ['name' => 'Sala nueva', 'experience_id' => $experience->id, 'location' => 'Murcia', 'capacity' => 6, 'status' => 'operational'];
    $this->actingAs($this->admin)->post(route('administration.rooms.store'), $data)->assertRedirect();
    $room = Room::where('name', 'Sala nueva')->sole();
    $this->get(route('administration.rooms.edit', $room))->assertOk();
    $this->patch(route('administration.rooms.update', $room), ['name' => 'Sala editada'] + $data)->assertRedirect();
    $this->get(route('administration.sessions.create'))->assertOk();
    $sessionData = ['room_experience_id' => $room->installations()->sole()->id, 'game_master_id' => $master->id,
        'starts_at' => '2026-11-14T15:00', 'ends_at' => '2026-11-14T16:00', 'locale' => 'es'];
    $this->post(route('administration.sessions.store'), $sessionData)->assertRedirect();
    $session = GameSession::sole();
    expect($session->preparation_minutes)->toBe(30)->and($session->starts_at->format('H:i'))->toBe('14:00');
    $this->get(route('administration.sessions.edit', $session))->assertOk();
    $this->patch(route('administration.sessions.update', $session), ['starts_at' => '2026-11-14T17:00', 'ends_at' => '2026-11-14T18:00'] + $sessionData)->assertRedirect();
    $this->delete(route('administration.sessions.destroy', $session))->assertRedirect();
    expect($session->fresh()->status)->toBe('cancelled');
    $this->delete(route('administration.rooms.destroy', $room))->assertRedirect();
    expect($room->fresh()->archived_at)->not->toBeNull();
});

test('booking normal entry point shows live availability and supports confirmation and group update', function () {
    $session = GameSession::factory()->create(['starts_at' => now()->addDays(2), 'ends_at' => now()->addDays(2)->addHour(), 'preparation_minutes' => 30]);
    Rate::factory()->create(['experience_id' => $session->experience_id]);
    $three = Rate::factory()->create(['experience_id' => $session->experience_id, 'players' => 3, 'total_cents' => 12000]);
    $customer = User::factory()->create()->assignRole('cliente');
    $this->actingAs($customer)->get(route('experiences.show', $session->experience->slug))->assertOk()->assertSee('Reservar para 2 personas');
    $this->post(route('cliente.reservas.store'), ['game_session_id' => $session->id, 'players' => 2])->assertRedirect();
    $reservation = $customer->reservations()->sole();
    $this->get(route('cliente.reservas.show', $reservation))->assertOk()->assertSee('Bloqueo pendiente');
    $this->actingAs($this->admin)->get(route('personal.reservas.show', $reservation))->assertOk()->assertSee('Confirmar reserva');
    $this->patch(route('personal.reservas.update', $reservation), ['players' => 3, 'total_cents' => 1])->assertRedirect();
    expect($reservation->fresh()->total_cents)->toBe($three->total_cents);
    $this->patch(route('personal.reservas.confirm', $reservation))->assertRedirect();
    expect($reservation->fresh()->status)->toBe('confirmed');
    $this->patch(route('personal.reservas.update', $reservation), ['players' => 2])->assertForbidden();
});

test('upcoming reservation filters preserve ownership and leave the full history accessible', function () {
    $customer = User::factory()->create()->assignRole('cliente');
    $pastSession = GameSession::factory()->create(['starts_at' => now()->subDays(2), 'ends_at' => now()->subDays(2)->addHour()]);
    $futureSession = GameSession::factory()->create(['starts_at' => now()->addDays(2), 'ends_at' => now()->addDays(2)->addHour()]);
    $past = Reservation::factory()->for($customer)->for($pastSession)->create();
    $future = Reservation::factory()->for($customer)->for($futureSession)->create();
    $foreign = Reservation::factory()->create();

    $this->actingAs($customer)->get(route('cliente.reservas.index'))->assertOk()
        ->assertSee($past->reference)->assertSee($future->reference)->assertDontSee($foreign->reference);
    $this->get(route('cliente.reservas.index', ['upcoming' => 1]))->assertOk()
        ->assertDontSee($past->reference)->assertSee($future->reference)->assertDontSee($foreign->reference);
    $this->get(route('cliente.reservas.index', ['upcoming' => 'invalid']))->assertSessionHasErrors('upcoming');
});

test('maintenance date filters use full Madrid days without exposing another technicians incident', function () {
    $technician = User::factory()->create()->assignRole('mantenimiento');
    $incident = Incident::factory()->create(['assigned_to' => $technician->id]);
    $inside = MaintenanceAction::factory()->for($incident)->create(['description' => 'Dentro del periodo', 'performed_at' => '2026-11-13 22:59:59']);
    $outside = MaintenanceAction::factory()->for($incident)->create(['description' => 'Fuera del periodo', 'performed_at' => '2026-11-13 23:00:00']);
    $filters = ['from' => '2026-11-13', 'until' => '2026-11-13'];

    $this->actingAs($technician)->get(route('mantenimiento.incidencias.show', [$incident] + $filters))->assertOk()
        ->assertSee($inside->description)->assertDontSee($outside->description);
    $this->get(route('mantenimiento.incidencias.show', [$incident, 'from' => '2026-11-13']))->assertSessionHasErrors('until');
    $foreign = Incident::factory()->create();
    $this->get(route('mantenimiento.incidencias.show', [$foreign] + $filters))->assertNotFound();
});
