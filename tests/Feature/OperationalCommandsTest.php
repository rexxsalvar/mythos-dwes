<?php

use App\Models\AvailabilityBlock;
use App\Models\GameSession;
use App\Models\Incident;
use App\Models\IncidentImpact;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use App\Services\ReviewAffectedReservationsService;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessRolesSeeder;
use Illuminate\Console\Command;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-13 10:00:00', 'UTC'));
});

test('expiry command respects the exact deadline preserves legacy holds and is repeatable', function () {
    $expired = Reservation::factory()->create(['expires_at' => now()]);
    $live = Reservation::factory()->create(['expires_at' => now()->addSecond()]);
    $legacy = Reservation::factory()->create(['expires_at' => null]);
    $confirmed = Reservation::factory()->create(['status' => 'confirmed', 'expires_at' => now()->subMinute()]);

    $this->artisan('reservations:expire')->expectsOutput('Reservas caducadas: 1')->assertSuccessful();
    $this->artisan('reservations:expire')->expectsOutput('Reservas caducadas: 0')->assertSuccessful();

    expect($expired->fresh()->status)->toBe('expired')
        ->and($expired->events()->where('action', 'expired')->count())->toBe(1)
        ->and($expired->events()->sole()->before_values['status'])->toBe('pending')
        ->and($live->fresh()->status)->toBe('pending')
        ->and($legacy->fresh()->status)->toBe('pending')
        ->and($confirmed->fresh()->status)->toBe('confirmed');
});

test('incident review marks future confirmations and records each impact once without cancelling bookings', function () {
    $session = GameSession::factory()->create(['starts_at' => now()->addDay(), 'ends_at' => now()->addDay()->addHour()]);
    $reservation = Reservation::factory()->for($session)->create(['status' => 'confirmed']);
    $incident = Incident::factory()->for($session->room)->create(['severity' => 'critical']);
    $untouched = Reservation::factory()->create(['status' => 'confirmed', 'needs_review' => true]);
    $pendingSession = GameSession::factory()->create(['starts_at' => now()->addDays(2), 'ends_at' => now()->addDays(2)->addHour()]);
    $pending = Reservation::factory()->for($pendingSession)->create(['expires_at' => now()->addMinutes(10)]);
    Incident::factory()->for($pendingSession->room)->create(['severity' => 'critical']);

    $this->artisan('incidents:review-reservations')->assertSuccessful();
    $this->artisan('incidents:review-reservations')->expectsOutput('Reservas afectadas: 1. Nuevas marcas de revisión: 0. Incidencias vinculadas: 0.')->assertSuccessful();

    expect($reservation->fresh()->needs_review)->toBeTrue()
        ->and($reservation->fresh()->status)->toBe('confirmed')
        ->and($reservation->events()->where('action', 'review_required')->count())->toBe(1)
        ->and(IncidentImpact::where('incident_id', $incident->id)->count())->toBe(1)
        ->and($pending->fresh()->needs_review)->toBeFalse()
        ->and($untouched->fresh()->needs_review)->toBeTrue();
});

test('staff blocks during preparation require review even without an incident and do not get released', function () {
    $session = GameSession::factory()->create(['starts_at' => now()->addDay(), 'ends_at' => now()->addDay()->addHour(), 'preparation_minutes' => 30]);
    $reservation = Reservation::factory()->for($session)->create(['status' => 'confirmed']);
    $block = AvailabilityBlock::factory()->create([
        'room_id' => null, 'user_id' => $session->game_master_id, 'kind' => 'staff_unavailable',
        'starts_at' => $session->starts_at->subMinutes(31), 'ends_at' => $session->starts_at->subMinutes(29),
    ]);

    $this->artisan('incidents:review-reservations')->assertSuccessful();

    expect($reservation->fresh()->needs_review)->toBeTrue()
        ->and($reservation->incidentImpacts()->count())->toBe(0)
        ->and($block->fresh()->released_at)->toBeNull();
});

test('maintenance command database failures return failure instead of a success message', function (string $command, string $service, string $method) {
    $this->mock($service, fn ($mock) => $mock->shouldReceive($method)->once()->andThrow(new PDOException('test database unavailable')));

    $this->artisan($command)->assertExitCode(Command::FAILURE);
})->with([
    ['reservations:expire', ReservationService::class, 'expirePending'],
    ['incidents:review-reservations', ReviewAffectedReservationsService::class, 'review'],
]);

test('web command entry points enforce authentication and administration before making changes', function () {
    $this->seed(AccessRolesSeeder::class);
    $expired = Reservation::factory()->create(['expires_at' => now()->subMinute()]);
    foreach (['expire', 'review', 'monthly'] as $operation) {
        $this->post(route('administration.operations.'.$operation))->assertRedirect(route('login'));
    }
    $customer = User::factory()->create()->assignRole('cliente');
    foreach (['expire', 'review', 'monthly'] as $operation) {
        $this->actingAs($customer)->post(route('administration.operations.'.$operation))->assertForbidden();
    }
    expect($expired->fresh()->status)->toBe('pending');
    $admin = User::factory()->create()->assignRole('administrador');
    $this->actingAs($admin)->post(route('administration.operations.expire'))->assertRedirect(route('administration.index'))
        ->assertSessionHas('status', 'Reservas caducadas: 1');
    $this->post(route('administration.operations.review'))->assertRedirect(route('administration.index'));
    expect($expired->fresh()->status)->toBe('expired');
});

test('monthly command runs from the validated administrative form and returns data rather than a false success', function () {
    $this->seed(AccessRolesSeeder::class);
    $admin = User::factory()->create()->assignRole('administrador');
    $this->actingAs($admin)->get(route('administration.index'))->assertOk()->assertSee('Descargar datos de actividad');
    $this->post(route('administration.operations.monthly'), ['month' => '2026-13'])->assertSessionHasErrors('month');
    $this->post(route('administration.operations.monthly'), ['month' => '2026-11', 'room_id' => -1])->assertSessionHasErrors('room_id');
    $response = $this->post(route('administration.operations.monthly'), ['month' => '2026-11'])->assertOk()
        ->assertHeader('Content-Type', 'application/json; charset=UTF-8');
    expect(json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR))->toBeArray();

    $this->mock(ReservationService::class, fn ($mock) => $mock->shouldReceive('expirePending')->once()->andThrow(new PDOException('test unavailable')));
    $this->post(route('administration.operations.expire'))->assertSessionHasErrors('operations');
});
