<?php

use App\Models\GameSession;
use App\Models\PaymentTransaction;
use App\Models\Rate;
use App\Models\Reservation;
use App\Models\ReservationParticipant;
use App\Models\Room;
use App\Services\ActivityReportService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;

test('monthly reports use half open Madrid month boundaries and optional room filtering', function () {
    $included = GameSession::factory()->create([
        'starts_at' => '2026-10-31 23:00:00', 'ends_at' => '2026-11-01 00:00:00',
        'preparation_minutes' => 30,
    ]);
    GameSession::factory()->create(['starts_at' => '2026-10-31 22:59:59', 'ends_at' => '2026-10-31 23:59:59']);
    GameSession::factory()->create(['starts_at' => '2026-11-30 23:00:00', 'ends_at' => '2026-12-01 00:00:00']);
    GameSession::factory()->create(['starts_at' => '2026-11-30 22:00:00', 'ends_at' => '2026-11-30 23:00:00', 'status' => 'cancelled']);

    $report = app(ActivityReportService::class)->monthly('2026-11');
    expect($report['session_count'])->toBe(2)
        ->and($report['from_utc'])->toBe('2026-10-31T23:00:00+00:00')
        ->and($report['until_utc_exclusive'])->toBe('2026-11-30T23:00:00+00:00')
        ->and($report['sessions_by_status']['scheduled'])->toBe(1)
        ->and($report['sessions_by_status']['cancelled'])->toBe(1)
        ->and($report['programmed_workload_by_game_master'])->toBe([[
            'game_master_id' => $included->game_master_id, 'session_count' => 1, 'game_minutes' => 60,
            'recorded_preparation_minutes' => 30, 'sessions_without_preparation_minutes' => 0,
        ]]);

    expect(app(ActivityReportService::class)->monthly('2026-11', $included->room_id)['session_count'])->toBe(1)
        ->and(app(ActivityReportService::class)->monthly('2026-12', $included->room_id)['session_count'])->toBe(0);
});

test('daily reports observe the short daylight saving day instead of adding 24 UTC hours', function () {
    GameSession::factory()->create(['starts_at' => '2026-03-28 23:00:00', 'ends_at' => '2026-03-29 00:00:00']);
    GameSession::factory()->create(['starts_at' => '2026-03-29 21:59:59', 'ends_at' => '2026-03-29 22:59:59']);
    GameSession::factory()->create(['starts_at' => '2026-03-29 22:00:00', 'ends_at' => '2026-03-29 23:00:00']);

    $report = app(ActivityReportService::class)->daily('2026-03-29');
    expect($report['session_count'])->toBe(2)
        ->and($report['from_utc'])->toBe('2026-03-28T23:00:00+00:00')
        ->and($report['until_utc_exclusive'])->toBe('2026-03-29T22:00:00+00:00')
        ->and($report['until_exclusive'])->toBe('2026-03-30');
});

test('activity totals retain booking prices and players without multiplying related records or currencies', function () {
    $session = GameSession::factory()->create([
        'starts_at' => '2026-11-13 10:00:00', 'ends_at' => '2026-11-13 11:00:00',
    ]);
    $rate = Rate::factory()->create(['experience_id' => $session->experience_id, 'players' => 3, 'total_cents' => 12345]);
    $confirmed = Reservation::factory()->create([
        'game_session_id' => $session->id, 'rate_id' => $rate->id, 'status' => 'confirmed',
        'players' => 3, 'total_cents' => 12345, 'contact_name' => 'Dato privado',
    ]);
    ReservationParticipant::factory()->create(['reservation_id' => $confirmed->id, 'position' => 1]);
    ReservationParticipant::factory()->create(['reservation_id' => $confirmed->id, 'position' => 2]);
    PaymentTransaction::factory()->create(['reservation_id' => $confirmed->id, 'amount_cents' => 5000]);
    PaymentTransaction::factory()->create(['reservation_id' => $confirmed->id, 'amount_cents' => 5000]);
    $rate->update(['players' => 4, 'total_cents' => 25000]);
    Reservation::factory()->create(['game_session_id' => $session->id, 'status' => 'cancelled', 'players' => 5, 'total_cents' => 90000]);
    $usdSession = GameSession::factory()->create(['starts_at' => '2026-11-13 12:00:00', 'ends_at' => '2026-11-13 13:00:00']);
    Reservation::factory()->create(['game_session_id' => $usdSession->id, 'status' => 'completed', 'players' => 2, 'total_cents' => 7500, 'currency' => 'USD']);
    $pendingSession = GameSession::factory()->create(['starts_at' => '2026-11-13 14:00:00', 'ends_at' => '2026-11-13 15:00:00']);
    Reservation::factory()->create(['game_session_id' => $pendingSession->id, 'status' => 'pending']);

    $report = app(ActivityReportService::class)->daily('2026-11-13');
    expect($report['confirmed_or_completed_reservations_by_currency'])->toBe([
        'EUR' => ['reservation_count' => 1, 'players' => 3, 'total_cents' => 12345],
        'USD' => ['reservation_count' => 1, 'players' => 2, 'total_cents' => 7500],
    ])->and($report['session_count'])->toBe(3)
        ->and($report['sessions_with_confirmed_or_completed_reservation'])->toBe(2)
        ->and(json_encode($report))->not->toContain('Dato privado')
        ->and(array_sum(array_column($report['programmed_workload_by_game_master'], 'sessions_without_preparation_minutes')))->toBe(3);
});

test('report commands produce parseable JSON and choose the current Madrid period', function () {
    $this->travelTo(CarbonImmutable::parse('2026-11-30 23:30:00', 'UTC'));
    foreach (['reports:daily' => '2026-12-01', 'reports:monthly' => '2026-12-01'] as $command => $expectedStart) {
        expect(Artisan::call($command))->toBe(Command::SUCCESS);
        $report = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
        expect($report['from'])->toBe($expectedStart)->and($report['session_count'])->toBe(0);
    }
    $room = Room::factory()->create();
    expect(Artisan::call('reports:monthly', ['month' => '2026-11', '--room' => $room->id]))->toBe(Command::SUCCESS);
    $report = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);
    expect($report['filters']['room_id'])->toBe($room->id);
});

test('report commands reject invalid periods and invalid rooms without querying arbitrary identifiers', function (string $command, array $arguments) {
    expect(Artisan::call($command, $arguments))->toBe(Command::INVALID)
        ->and(trim(Artisan::output()))->not->toBe('');
})->with([
    ['reports:daily', ['date' => '2026-02-30']],
    ['reports:daily', ['date' => '2026-2-01']],
    ['reports:daily', ['date' => 'tomorrow']],
    ['reports:daily', ['date' => '2026-11-01', '--room' => '999999']],
    ['reports:monthly', ['month' => '2026-13']],
    ['reports:monthly', ['month' => '2026-11-01']],
    ['reports:monthly', ['month' => '2026-11', '--room' => '0']],
    ['reports:monthly', ['month' => '2026-11', '--room' => '1.5']],
]);

test('report commands return failure when activity data cannot be read', function () {
    $this->mock(ActivityReportService::class, function ($mock) {
        $failure = new QueryException('sqlite', 'select 1', [], new RuntimeException('Simulated storage failure'));
        $mock->shouldReceive('daily')->once()->andThrow($failure);
        $mock->shouldReceive('monthly')->once()->andThrow($failure);
    });
    expect(Artisan::call('reports:daily', ['date' => '2026-11-13']))->toBe(Command::FAILURE)
        ->and(Artisan::output())->not->toContain('Simulated storage failure');
    expect(Artisan::call('reports:monthly', ['month' => '2026-11']))->toBe(Command::FAILURE);
});
