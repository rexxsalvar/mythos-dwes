<?php

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
use App\Models\TeamMembership;
use App\Models\User;
use Illuminate\Database\QueryException;

test('all domain factories create valid linked models', function (string $model) {
    expect($model::factory()->create()->exists)->toBeTrue();
})->with([
    Experience::class, Room::class, Rate::class, Team::class, GameSession::class,
    Reservation::class, Hint::class, PreparationCheck::class, GameResult::class,
    Incident::class, MaintenanceAction::class, User::class,
]);

test('catalogue sessions bookings and staff expose both sides of one to many relations', function () {
    $session = GameSession::factory()->create();
    $booking = Reservation::factory()->for($session)->create();
    $rate = Rate::factory()->for($session->experience)->create();
    expect($session->room->experience->is($session->experience))->toBeTrue()
        ->and($session->experience->rooms->contains($session->room))->toBeTrue()
        ->and($session->experience->rates->contains($rate))->toBeTrue()
        ->and($rate->experience->is($session->experience))->toBeTrue()
        ->and($session->experience->gameSessions->contains($session))->toBeTrue()
        ->and($session->room->gameSessions->contains($session))->toBeTrue()
        ->and($session->gameMaster->assignedSessions->contains($session))->toBeTrue()
        ->and($session->reservations->contains($booking))->toBeTrue()
        ->and($booking->user->reservations->contains($booking))->toBeTrue();
});

test('team membership attaches reads updates and detaches pivot values from code', function () {
    $team = Team::factory()->create();
    $member = User::factory()->create();
    $team->users()->attach($member, ['role' => 'member', 'joined_at' => '2026-10-01 10:00:00']);
    $pivot = $team->users()->firstOrFail()->pivot;
    expect($pivot)->toBeInstanceOf(TeamMembership::class)
        ->and($pivot->role)->toBe('member')
        ->and($pivot->joined_at->toDateString())->toBe('2026-10-01')
        ->and($member->teams->first()->is($team))->toBeTrue()
        ->and($team->owner->ownedTeams->contains($team))->toBeTrue();
    $team->users()->updateExistingPivot($member->id, ['role' => 'captain']);
    expect($team->users()->first()->pivot->role)->toBe('captain');
    $booking = Reservation::factory()->for($team)->create();
    expect($team->reservations->contains($booking))->toBeTrue()->and($booking->team->is($team))->toBeTrue();
    $team->users()->detach($member);
    expect($team->users()->count())->toBe(0);
});

test('hint usage handles its timestamp and nullable penalty in both directions', function () {
    $session = GameSession::factory()->create();
    $hint = Hint::factory()->for($session->experience)->create();
    $session->hints()->attach($hint, ['used_at' => '2026-10-17 10:10:00', 'penalty_seconds' => null]);
    expect($session->hints()->first()->pivot->penalty_seconds)->toBeNull()
        ->and($session->hints()->first()->pivot->used_at->toDateString())->toBe('2026-10-17')
        ->and($hint->gameSessions->first()->is($session))->toBeTrue()
        ->and($hint->experience->hints->contains($hint))->toBeTrue();
    // Synthetic test value, not a decision about business penalties.
    $session->hints()->updateExistingPivot($hint->id, ['penalty_seconds' => 30]);
    expect($hint->gameSessions()->first()->pivot->penalty_seconds)->toBe(30);
    $session->hints()->detach($hint);
    expect($session->hints()->count())->toBe(0);
});

test('preparation result and maintenance preserve their relationships', function () {
    $session = GameSession::factory()->create(['status' => 'completed']);
    $check = PreparationCheck::factory()->for($session)->create([
        'checked_by' => $session->game_master_id, 'checked_at' => $session->starts_at, 'passed' => true,
    ]);
    $result = GameResult::factory()->for($session)->create();
    $incident = Incident::factory()->for($session->room)->for($session)->create();
    $action = MaintenanceAction::factory()->for($incident)->create();
    expect($session->preparationChecks->contains($check))->toBeTrue()
        ->and($check->checker->is($session->gameMaster))->toBeTrue()
        ->and($session->result->is($result))->toBeTrue()
        ->and($result->recorder->is($session->gameMaster))->toBeTrue()
        ->and($session->incidents->contains($incident))->toBeTrue()
        ->and($incident->room->incidents->contains($incident))->toBeTrue()
        ->and($incident->reporter->reportedIncidents->contains($incident))->toBeTrue()
        ->and($incident->maintenanceActions->contains($action))->toBeTrue()
        ->and($action->technician->maintenanceActions->contains($action))->toBeTrue();
    $incident->update(['assigned_to' => $action->technician_id]);
    expect($incident->fresh()->assignee->assignedIncidents->contains($incident))->toBeTrue();
});

test('a user cannot be attached twice to the same team', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();
    $values = ['role' => 'member', 'joined_at' => now()];
    $team->users()->attach($user, $values);
    expect(fn () => $team->users()->attach($user, $values))->toThrow(QueryException::class);
});
