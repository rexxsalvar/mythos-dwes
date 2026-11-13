<?php

use App\Models\AvailabilityBlock;
use App\Models\Experience;
use App\Models\GameSession;
use App\Models\Incident;
use App\Models\MaintenanceAction;
use App\Models\Rate;
use App\Models\RatePlan;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\CarbonImmutable;

test('catalogue and upcoming queries keep publication and session time separate', function () {
    $instant = CarbonImmutable::parse('2026-11-10 10:00:00', 'UTC');
    $published = Experience::factory()->create();
    Experience::factory()->create(['is_active' => false]);
    Experience::factory()->create(['archived_at' => $instant]);
    $room = Room::factory()->for($published)->create();
    $pastSession = GameSession::factory()->for($room)->create([
        'starts_at' => $instant->subHours(2),
        'ends_at' => $instant->subHour(),
    ]);
    $futureSession = GameSession::factory()->for($room)->create([
        'starts_at' => $instant->addHour(),
        'ends_at' => $instant->addHours(2),
    ]);
    $owner = User::factory()->create();
    Reservation::factory()->for($owner)->for($pastSession)->create();
    $upcoming = Reservation::factory()->for($owner)->for($futureSession)->create();

    expect(Experience::published()->sole()->id)->toBe($published->id)
        ->and($owner->reservations()->upcoming($instant)->sole()->id)->toBe($upcoming->id);
});

test('active reservations respect scope boundaries and legacy deadlines', function () {
    $instant = CarbonImmutable::parse('2026-11-10 10:00:00', 'UTC');
    $owner = User::factory()->create();
    $other = Reservation::factory()->create(['status' => 'pending', 'expires_at' => $instant->addMinute()]);
    $undated = Reservation::factory()->for($owner)->create(['status' => 'pending', 'expires_at' => null]);
    Reservation::factory()->for($owner)->create(['status' => 'pending', 'expires_at' => $instant]);
    $confirmed = Reservation::factory()->for($owner)->create(['status' => 'confirmed', 'confirmed_at' => $instant->subMinute()]);

    $matches = $owner->reservations()->activeAt($instant)->pluck('reservations.id');

    expect($matches->all())->toEqualCanonicalizing([$undated->id, $confirmed->id])
        ->and($matches->contains($other->id))->toBeFalse();
});

test('availability considers preparation, capacity, blocks, incidents and its own pending confirmation', function () {
    $start = CarbonImmutable::parse('2026-11-10 11:00:00', 'UTC');
    $instant = $start->subHour();
    $session = GameSession::factory()->create([
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(75),
        'preparation_minutes' => 30,
    ]);
    $available = fn (int $players = 2, ?int $exceptReservationId = null) => GameSession::query()
        ->availableBetween($start, $start->addDay(), $session->experience_id, $players, $instant, exceptReservationId: $exceptReservationId)
        ->whereKey($session->id)->exists();

    expect($available())->toBeTrue()
        ->and($available(players: 7))->toBeFalse()
        ->and(GameSession::query()->availableBetween($start, $start->addDay(), $session->experience_id, 2, $start->subMinutes(30))->exists())->toBeFalse();

    // A block ending exactly at preparation does not overlap the occupied interval.
    AvailabilityBlock::factory()->for($session->room)->create([
        'starts_at' => $instant,
        'ends_at' => $start->subMinutes(30),
    ]);
    expect($available())->toBeTrue();

    $staffBlock = AvailabilityBlock::factory()->create([
        'room_id' => null,
        'user_id' => $session->game_master_id,
        'starts_at' => $instant,
        'ends_at' => $start->subMinutes(29),
        'kind' => 'staff_unavailable',
    ]);
    expect($available())->toBeFalse();
    $staffBlock->update(['released_at' => $instant, 'released_by' => $session->game_master_id]);

    $incident = Incident::factory()->for($session->room)->create(['severity' => 'critical']);
    expect($available())->toBeFalse();
    $incident->update(['status' => 'resolved', 'resolved_at' => $instant]);

    $pending = Reservation::factory()->for($session)->create(['expires_at' => $instant->addMinutes(15)]);
    expect($available())->toBeFalse()
        ->and($available(exceptReservationId: $pending->id))->toBeTrue();
    $pending->update(['expires_at' => $instant]);
    expect($available())->toBeTrue();
});

test('effective rates use matching groups and half-open published plan periods in UTC', function () {
    $experience = Experience::factory()->create();
    $start = CarbonImmutable::parse('2026-11-10 10:00:00', 'UTC');
    $end = $start->addHour();
    $plan = RatePlan::factory()->for($experience)->create(['valid_from' => $start, 'valid_until' => $end]);
    $rate = Rate::factory()->for($plan)->for($experience)->create(['players' => 3]);
    $draft = RatePlan::factory()->for($experience)->create(['valid_from' => $start, 'status' => 'draft']);
    Rate::factory()->for($draft)->for($experience)->create(['players' => 3]);

    expect(Rate::effectiveForGroup($experience->id, 3, 'EUR', $start->setTimezone('Europe/Madrid'))->sole()->id)->toBe($rate->id)
        ->and(Rate::effectiveForGroup($experience->id, 2, 'EUR', $start)->exists())->toBeFalse()
        ->and(Rate::effectiveForGroup($experience->id, 3, 'USD', $start)->exists())->toBeFalse()
        ->and(Rate::effectiveForGroup($experience->id, 3, 'EUR', $end)->exists())->toBeFalse();
});

test('maintenance queries select unresolved incidents and the assigned technician reporting window', function () {
    $instant = CarbonImmutable::parse('2026-11-10 10:00:00', 'UTC');
    $until = $instant->addHour();
    $incident = Incident::factory()->create(['status' => 'in_progress', 'severity' => 'high']);
    Incident::factory()->for($incident->room)->create(['status' => 'resolved', 'resolved_at' => $instant, 'severity' => 'critical']);
    Incident::factory()->create(['severity' => 'critical']);
    $action = MaintenanceAction::factory()->for($incident)->create(['performed_at' => $instant]);
    MaintenanceAction::factory()->for($incident)->create(['performed_at' => $instant]);
    MaintenanceAction::factory()->for($incident)->create(['technician_id' => $action->technician_id, 'performed_at' => $until]);

    expect(Incident::requiringAttention(roomId: $incident->room_id)->sole()->id)->toBe($incident->id)
        ->and($incident->maintenanceActions()->performedBetween($instant, $until, $action->technician_id)->sole()->id)->toBe($action->id);
});

test('upcoming periods include the lower boundary and exclude the upper boundary without changing default states', function () {
    $instant = CarbonImmutable::parse('2026-11-13 10:00:00', 'UTC');
    $until = $instant->addHour();
    $owner = User::factory()->create();
    $inside = [];

    foreach ([-1, 0, 30, 60] as $minutes) {
        $session = GameSession::factory()->create([
            'starts_at' => $instant->addMinutes($minutes),
            'ends_at' => $instant->addMinutes($minutes + 75),
        ]);
        $reservation = Reservation::factory()->for($owner)->for($session)->create(['status' => 'cancelled']);
        if ($minutes >= 0 && $minutes < 60) {
            $inside[] = $reservation->id;
        }
    }

    expect($owner->reservations()->upcoming($instant->setTimezone('Europe/Madrid'), $until)->pluck('reservations.id')->all())
        ->toEqualCanonicalizing($inside)
        ->and($owner->reservations()->upcoming($instant)->count())->toBe(3)
        ->and($owner->reservations()->upcoming($instant, affectedOnly: true)->exists())->toBeFalse();
});

test('critical incidents select only future confirmed reservations of the affected room', function (string $state, int $offset, string $incidentState, bool $expected) {
    $instant = CarbonImmutable::parse('2026-11-13 10:00:00', 'UTC');
    $session = GameSession::factory()->create([
        'starts_at' => $instant->addMinutes($offset),
        'ends_at' => $instant->addMinutes($offset + 75),
    ]);
    $reservation = Reservation::factory()->for($session)->create(['status' => $state]);
    $incident = Incident::factory()->for($session->room)->create([
        'severity' => 'critical',
        'status' => $incidentState,
        'resolved_at' => $incidentState === 'resolved' ? $instant : null,
    ]);
    Incident::factory()->create(['severity' => 'critical']);

    expect(Reservation::upcoming($instant, affectedOnly: true)->whereKey($reservation->id)->exists())->toBe($expected)
        ->and(Incident::requiringAttention(severity: 'critical', reservationsAfter: $instant)->whereKey($incident->id)->exists())->toBe($expected);
})->with([
    'confirmed at boundary' => ['confirmed', 0, 'open', true],
    'confirmed in future' => ['confirmed', 60, 'in_progress', true],
    'confirmed in past' => ['confirmed', -1, 'open', false],
    'pending in future' => ['pending', 60, 'open', false],
    'cancelled in future' => ['cancelled', 60, 'open', false],
    'resolved incident' => ['confirmed', 60, 'resolved', false],
]);

test('affected reservations use half-open room and staff blocks including preparation', function (string $resource, int $blockStart, ?int $blockEnd, bool $released, bool $expected) {
    $instant = CarbonImmutable::parse('2026-11-13 10:00:00', 'UTC');
    $start = $instant->addHour();
    $session = GameSession::factory()->create([
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(75),
        'preparation_minutes' => 30,
    ]);
    $reservation = Reservation::factory()->for($session)->create(['status' => 'confirmed']);
    AvailabilityBlock::factory()->create([
        'room_id' => $resource === 'room' ? $session->room_id : null,
        'user_id' => match ($resource) {
            'staff' => $session->game_master_id,
            'unrelated' => User::factory()->create()->id,
            default => null,
        },
        'starts_at' => $start->addMinutes($blockStart),
        'ends_at' => $blockEnd === null ? null : $start->addMinutes($blockEnd),
        'released_at' => $released ? $instant : null,
        'released_by' => $released ? $session->game_master_id : null,
    ]);

    expect(Reservation::upcoming($instant, affectedOnly: true)->whereKey($reservation->id)->exists())->toBe($expected);
})->with([
    'room intersects preparation' => ['room', -40, -29, false, true],
    'staff intersects preparation' => ['staff', -40, -29, false, true],
    'room ends at preparation' => ['room', -40, -30, false, false],
    'staff starts at session end' => ['staff', 75, 90, false, false],
    'indefinite block' => ['room', -40, null, false, true],
    'released block' => ['room', -40, 90, true, false],
    'unrelated staff block' => ['unrelated', -40, 90, false, false],
]);
