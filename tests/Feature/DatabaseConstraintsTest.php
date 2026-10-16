<?php

use App\Models\Experience;
use App\Models\GameResult;
use App\Models\GameSession;
use App\Models\Hint;
use App\Models\Incident;
use App\Models\PreparationCheck;
use App\Models\Rate;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

test('foreign keys reject orphan bookings and preserve booking history on deletion', function () {
    $booking = Reservation::factory()->create();
    expect(fn () => $booking->user->delete())->toThrow(QueryException::class);
    expect(fn () => $booking->gameSession->delete())->toThrow(QueryException::class);
    expect(fn () => $booking->update(['user_id' => 999999]))->toThrow(QueryException::class);
});

test('active reservation uniqueness applies to inserts and status changes', function () {
    $booking = Reservation::factory()->create(['status' => 'confirmed']);
    expect(fn () => Reservation::factory()->for($booking->gameSession)->create())->toThrow(QueryException::class);
    $history = Reservation::factory()->for($booking->gameSession)->create(['status' => 'cancelled']);
    expect(fn () => $history->update(['status' => 'pending']))->toThrow(QueryException::class);
    $booking->update(['status' => 'cancelled']);
    $history->refresh()->update(['status' => 'pending']);
    expect($booking->fresh()->status)->toBe('cancelled')->and(Reservation::count())->toBe(2);
});

test('player and money constraints reject invalid data even through direct SQL', function (array $attributes) {
    $booking = Reservation::factory()->create();
    expect(fn () => DB::table('reservations')->where('id', $booking->id)->update($attributes))->toThrow(QueryException::class);
})->with([
    'zero players' => [['players' => 0]], 'below minimum' => [['players' => 1]],
    'above maximum' => [['players' => 7]], 'negative money' => [['total_cents' => -1]],
    'unknown state' => [['status' => 'invented']],
    'fractional players' => [['players' => 2.5]],
    'fractional cents' => [['total_cents' => 8000.5]],
]);

test('room capacity also restricts bookings', function () {
    $room = Room::factory()->create(['capacity' => 3]);
    $session = GameSession::factory()->for($room)->create();
    expect(fn () => Reservation::factory()->for($session)->create(['players' => 4]))->toThrow(QueryException::class);
});

test('rates are unique per group size and inside experience limits', function () {
    $rate = Rate::factory()->create();
    expect(fn () => Rate::factory()->for($rate->experience)->create())->toThrow(QueryException::class);
    expect(fn () => Rate::factory()->for($rate->experience)->create(['players' => 7]))->toThrow(QueryException::class);
});

test('experience limits and duration must be valid', function (array $attributes) {
    expect(fn () => Experience::factory()->create($attributes))->toThrow(QueryException::class);
})->with([
    [['duration_minutes' => 0]], [['min_players' => 0]], [['min_players' => 7, 'max_players' => 6]],
]);

test('session experience must match the physical room', function () {
    $experience = Experience::factory()->create();
    expect(fn () => GameSession::factory()->create(['experience_id' => $experience->id]))->toThrow(QueryException::class);
});

test('sessions reject overlapping room or game master on insert and update', function (string $resource) {
    $first = GameSession::factory()->create(['starts_at' => '2026-10-17 10:00:00', 'ends_at' => '2026-10-17 11:15:00']);
    $attributes = $resource === 'room'
        ? ['room_id' => $first->room_id, 'experience_id' => $first->experience_id]
        : ['game_master_id' => $first->game_master_id];
    expect(fn () => GameSession::factory()->create($attributes + ['starts_at' => '2026-10-17 11:00:00', 'ends_at' => '2026-10-17 12:15:00']))->toThrow(QueryException::class);
    $next = GameSession::factory()->create($attributes + ['starts_at' => '2026-10-17 11:15:00', 'ends_at' => '2026-10-17 12:30:00']);
    expect(fn () => $next->update(['starts_at' => '2026-10-17 11:10:00']))->toThrow(QueryException::class);
    expect(fn () => $next->refresh()->update(['preparation_minutes' => 10]))->toThrow(QueryException::class);
    $first->update(['status' => 'cancelled']);
    $next->refresh()->update(['starts_at' => '2026-10-17 11:10:00']);
    expect($next->fresh()->starts_at->format('H:i'))->toBe('11:10');
})->with(['room', 'master']);

test('a session cannot finish before it starts', function () {
    expect(fn () => GameSession::factory()->create(['starts_at' => '2026-10-17 10:00:00', 'ends_at' => '2026-10-17 09:00:00']))->toThrow(QueryException::class);
});

test('session timestamps require canonical UTC values for overlap comparisons', function () {
    $session = GameSession::factory()->create();
    expect(fn () => DB::table('game_sessions')->where('id', $session->id)->update(['starts_at' => 'not-a-date']))->toThrow(QueryException::class);
});

test('a result is unique per session', function () {
    $result = GameResult::factory()->create();
    expect(fn () => GameResult::factory()->for($result->gameSession)->create())->toThrow(QueryException::class);
});

test('an incident cannot reference a session in another room', function () {
    $session = GameSession::factory()->create();
    expect(fn () => Incident::factory()->create(['game_session_id' => $session->id]))->toThrow(QueryException::class);
});

test('hint usage cannot cross experiences or use a negative penalty', function () {
    $session = GameSession::factory()->create();
    $foreignHint = Hint::factory()->create();
    expect(fn () => $session->hints()->attach($foreignHint, ['used_at' => now()]))->toThrow(QueryException::class);
    $hint = Hint::factory()->for($session->experience)->create();
    expect(fn () => $session->hints()->attach($hint, ['used_at' => now(), 'penalty_seconds' => -1]))->toThrow(QueryException::class);
});

test('membership roles preparation and incident resolution reject inconsistent records', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();
    expect(fn () => $team->users()->attach($user, ['role' => 'invalid', 'joined_at' => now()]))->toThrow(QueryException::class);
    expect(fn () => PreparationCheck::factory()->create(['passed' => true]))->toThrow(QueryException::class);
    expect(fn () => Incident::factory()->create(['status' => 'resolved']))->toThrow(QueryException::class);
});
