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
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('demo seeding is reproducible idempotent and contains only fictitious people', function () {
    $this->seed();
    $models = [User::class => 6, Experience::class => 1, Room::class => 2, Rate::class => 5,
        Team::class => 1, GameSession::class => 5, Reservation::class => 5, Hint::class => 2,
        PreparationCheck::class => 3, GameResult::class => 1, Incident::class => 2, MaintenanceAction::class => 1];
    foreach ($models as $model => $expected) {
        expect($model::count())->toBe($expected);
    }
    $snapshot = Reservation::orderBy('reference')->get(['reference', 'status', 'players', 'total_cents'])->toArray();
    $this->seed();
    foreach ($models as $model => $expected) {
        expect($model::count())->toBe($expected);
    }
    expect(Reservation::orderBy('reference')->get(['reference', 'status', 'players', 'total_cents'])->toArray())->toBe($snapshot)
        ->and(DB::table('team_user')->count())->toBe(2)
        ->and(DB::table('game_session_hint')->count())->toBe(1)
        ->and(DB::select('PRAGMA foreign_key_check'))->toBe([])
        ->and(User::where('email', 'not like', '%@example.test')->count())->toBe(0)
        ->and(Rate::orderBy('players')->pluck('total_cents')->all())->toBe([8000, 9000, 10000, 11000, 13200]);
});
