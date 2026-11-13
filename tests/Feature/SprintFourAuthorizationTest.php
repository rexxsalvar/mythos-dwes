<?php

use App\Models\Experience;
use App\Models\GameSession;
use App\Models\Incident;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomInstallation;
use App\Models\User;
use Database\Seeders\AccessRolesSeeder;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->seed(AccessRolesSeeder::class);
});

test('catalogue policy exposes only active unarchived experiences and protects management', function () {
    $published = Experience::factory()->create();
    $inactive = Experience::factory()->create(['is_active' => false]);
    $archived = Experience::factory()->create(['archived_at' => now()]);
    $client = User::factory()->create()->assignRole('cliente');
    $admin = User::factory()->create()->assignRole('administrador');

    expect(Gate::allows('viewAny', Experience::class))->toBeTrue()
        ->and(Gate::allows('view', $published))->toBeTrue()
        ->and(Gate::allows('view', $inactive))->toBeFalse()
        ->and(Gate::allows('view', $archived))->toBeFalse()
        ->and(Gate::allows('create', Experience::class))->toBeFalse()
        ->and(Gate::forUser($client)->allows('update', $published))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('view', $archived))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $archived))->toBeTrue();

    $admin->is_active = false;
    expect(Gate::forUser($admin)->allows('update', $published))->toBeFalse();
});

test('room policy checks the current installation and protects catalogue writes', function () {
    $room = Room::factory()->create();
    $installation = RoomInstallation::factory()->create([
        'room_id' => $room->id,
        'experience_id' => $room->experience_id,
        'installed_from' => now('UTC')->subWeek(),
        'installed_until' => now('UTC')->addWeek(),
    ]);
    $reception = User::factory()->create()->assignRole('recepcion');
    $admin = User::factory()->create()->assignRole('administrador');

    expect(Gate::allows('view', $room))->toBeTrue()
        ->and(Gate::forUser($reception)->allows('operate', $room))->toBeFalse()
        ->and(Gate::forUser($reception)->allows('update', $room))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('operate', $room))->toBeTrue();

    $installation->update(['installed_until' => now('UTC')]);
    expect(Gate::allows('view', $room))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('view', $room))->toBeTrue();
});

test('session management and gameplay require different permissions and valid states', function () {
    $master = User::factory()->create()->assignRole('game_master');
    $reception = User::factory()->create()->assignRole('recepcion');
    $admin = User::factory()->create()->assignRole('administrador');
    $assigned = GameSession::factory()->create(['game_master_id' => $master->id]);
    $foreign = GameSession::factory()->create();
    $completed = GameSession::factory()->create(['status' => 'completed']);

    expect(Gate::forUser($master)->allows('operate', $assigned))->toBeTrue()
        ->and(Gate::forUser($master)->allows('operate', $foreign))->toBeFalse()
        ->and(Gate::forUser($master)->allows('update', $assigned))->toBeFalse()
        ->and(Gate::forUser($reception)->allows('create', GameSession::class))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('create', GameSession::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $assigned))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $completed))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('operate', $completed))->toBeFalse();
});

test('reservation writes check customer ownership delegated booking and reservation state', function () {
    $owner = User::factory()->create()->assignRole('cliente');
    $other = User::factory()->create()->assignRole('cliente');
    $reception = User::factory()->create()->assignRole('recepcion');
    $reservation = Reservation::factory()->for($owner)->create();

    expect(Gate::forUser($owner)->allows('createFor', [Reservation::class, $owner]))->toBeTrue()
        ->and(Gate::forUser($owner)->allows('createFor', [Reservation::class, $other]))->toBeFalse()
        ->and(Gate::forUser($reception)->allows('createFor', [Reservation::class, $other]))->toBeTrue()
        ->and(Gate::forUser($other)->allows('cancel', $reservation))->toBeFalse()
        ->and(Gate::forUser($owner)->allows('cancel', $reservation))->toBeTrue()
        ->and(Gate::forUser($owner)->allows('confirm', $reservation))->toBeFalse()
        ->and(Gate::forUser($reception)->allows('confirm', $reservation))->toBeTrue();

    $reservation->status = 'completed';
    expect(Gate::forUser($reception)->allows('cancel', $reservation))->toBeFalse()
        ->and(Gate::forUser($reception)->allows('confirm', $reservation))->toBeFalse();
});

test('incident policy allows assigned technicians to act and keeps historical incidents', function () {
    $technician = User::factory()->create()->assignRole('mantenimiento');
    $admin = User::factory()->create()->assignRole('administrador');
    $owned = Incident::factory()->create(['assigned_to' => $technician->id]);
    $unassigned = Incident::factory()->create();

    expect(Gate::forUser($technician)->allows('create', Incident::class))->toBeTrue()
        ->and(Gate::forUser($technician)->allows('update', $owned))->toBeTrue()
        ->and(Gate::forUser($technician)->allows('view', $unassigned))->toBeFalse()
        ->and(Gate::forUser($technician)->allows('update', $unassigned))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('update', $unassigned))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $owned))->toBeFalse()
        ->and(Gate::allows('viewAny', Incident::class))->toBeFalse();
});
