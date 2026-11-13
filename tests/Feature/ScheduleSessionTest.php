<?php

use App\Models\AvailabilityBlock;
use App\Models\GameSession;
use App\Models\Incident;
use App\Models\Reservation;
use App\Models\RoomInstallation;
use App\Models\User;
use App\Services\ScheduleSession;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessRolesSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(AccessRolesSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-11-13 09:00 UTC'));
    $this->admin = User::factory()->create()->assignRole('administrador');
    $this->master = User::factory()->create()->assignRole('game_master');
    $this->installation = RoomInstallation::factory()->create();
    $this->schedule = app(ScheduleSession::class);
    $this->start = CarbonImmutable::parse('2026-11-13 11:00 Europe/Madrid');
    $this->end = $this->start->addHour();
});

test('the agenda stores UTC and rejects overlaps for either resource but permits contiguous occupation', function () {
    $first = $this->schedule->create($this->admin, $this->installation, $this->master, $this->start, $this->end);
    expect($first->preparation_minutes)->toBe(30)
        ->and($first->fresh()->starts_at->toDateTimeString())->toBe('2026-11-13 10:00:00');

    $otherMaster = User::factory()->create()->assignRole('game_master');
    $otherRoom = RoomInstallation::factory()->create();
    $overlap = $this->end->addMinutes(29);
    $contiguous = $this->end->addMinutes(30);

    expect(fn () => $this->schedule->create($this->admin, $this->installation, $otherMaster, $overlap, $overlap->addHour()))
        ->toThrow(ValidationException::class, 'se solapan')
        ->and(fn () => $this->schedule->create($this->admin, $otherRoom, $this->master, $overlap, $overlap->addHour()))
        ->toThrow(ValidationException::class, 'se solapan');

    $next = $this->schedule->create($this->admin, $this->installation, $this->master, $contiguous, $contiguous->addHour());
    expect($next->starts_at->subMinutes(30)->eq($first->ends_at))->toBeTrue()
        ->and(GameSession::count())->toBe(2);
});

test('the agenda verifies the installation period and assigned staff before saving', function () {
    $this->installation->update(['installed_until' => $this->end->copy()->utc()->subSecond()]);
    expect(fn () => $this->schedule->create($this->admin, $this->installation, $this->master, $this->start, $this->end))
        ->toThrow(ValidationException::class, 'instalación');

    $this->installation->update(['installed_until' => $this->end->copy()->utc()]);
    $this->master->update(['is_active' => false]);
    expect(fn () => $this->schedule->create($this->admin, $this->installation, $this->master, $this->start, $this->end))
        ->toThrow(ValidationException::class, 'game master activo')
        ->and(GameSession::count())->toBe(0);

    $this->master->update(['is_active' => true]);
    expect(fn () => $this->schedule->create($this->master, $this->installation, $this->master, $this->start, $this->end))
        ->toThrow(AuthorizationException::class);
});

test('an active block during preparation or a critical incident prevents scheduling', function () {
    $block = AvailabilityBlock::factory()->create([
        'room_id' => null, 'user_id' => $this->master->id, 'kind' => 'staff_unavailable',
        'starts_at' => $this->start->utc()->subMinutes(31),
        'ends_at' => $this->start->utc()->subMinutes(29),
    ]);
    expect(fn () => $this->schedule->create($this->admin, $this->installation, $this->master, $this->start, $this->end))
        ->toThrow(ValidationException::class, 'bloqueo');

    $block->update(['released_at' => now(), 'released_by' => $this->admin->id]);
    $incident = Incident::factory()->create(['room_id' => $this->installation->room_id, 'severity' => 'critical']);
    expect(fn () => $this->schedule->create($this->admin, $this->installation, $this->master, $this->start, $this->end))
        ->toThrow(ValidationException::class, 'bloqueo');

    $incident->update(['status' => 'resolved', 'resolved_at' => now()]);
    expect($this->schedule->create($this->admin, $this->installation, $this->master, $this->start, $this->end)->status)->toBe('scheduled');
});

test('agenda changes preserve existing reservations and a cancelled session stays in history', function () {
    $session = $this->schedule->create($this->admin, $this->installation, $this->master, $this->start, $this->end);
    $newStart = $this->start->addHours(2);
    $session = $this->schedule->update($this->admin, $session, $this->installation, $this->master, $newStart, $newStart->addHour());
    expect($session->starts_at->eq($newStart))->toBeTrue();

    $reservation = Reservation::factory()->create(['game_session_id' => $session->id, 'status' => 'confirmed']);
    expect(fn () => $this->schedule->cancel($this->admin, $session))->toThrow(ValidationException::class, 'reserva activa');

    $reservation->update(['status' => 'cancelled']);
    expect(fn () => $this->schedule->update($this->admin, $session, $this->installation, $this->master, $this->start, $this->end))
        ->toThrow(ValidationException::class, 'historial de reservas');

    expect($this->schedule->cancel($this->admin, $session)->status)->toBe('cancelled')
        ->and($reservation->fresh()->gameSession->starts_at->eq($newStart))->toBeTrue();
});
