<?php

use App\Models\GameSession;
use App\Models\Rate;
use App\Models\Reservation;
use App\Models\RoomInstallation;
use App\Models\User;
use App\Services\ReservationService;
use App\Services\ScheduleSession;
use Database\Seeders\AccessRolesSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

test('reservation service handles a competing SQLite writer and rechecks availability on retry', function () {
    $path = tempnam(sys_get_temp_dir(), 'mythos-service-race-');
    $original = config('database.default');
    config(['database.connections.race_service' => array_replace(config('database.connections.sqlite'), ['database' => $path, 'busy_timeout' => 1])]);
    $writer = null;

    try {
        Artisan::call('migrate', ['--database' => 'race_service', '--force' => true]);
        config(['database.default' => 'race_service']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(AccessRolesSeeder::class);
        $customer = User::factory()->create()->assignRole('cliente');
        $admin = User::factory()->create()->assignRole('administrador');
        $master = User::factory()->create()->assignRole('game_master');
        $installation = RoomInstallation::factory()->create();
        $session = GameSession::factory()->create(['starts_at' => now()->addDay(), 'ends_at' => now()->addDay()->addHour(), 'preparation_minutes' => 30]);
        Rate::factory()->create(['experience_id' => $session->experience_id]);
        $writer = new PDO('sqlite:'.$path, options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $writer->exec('BEGIN IMMEDIATE');
        $service = app(ReservationService::class);
        expect(fn () => $service->hold($customer, $session, 2))->toThrow(ValidationException::class);
        expect(fn () => app(ScheduleSession::class)->create($admin, $installation, $master, now()->addDays(2), now()->addDays(2)->addHour()))
            ->toThrow(ValidationException::class, 'agenda está siendo actualizada');
        $writer->exec('ROLLBACK');

        $reservation = $service->hold($customer, $session, 2);
        expect(fn () => $service->hold($customer, $session, 2))->toThrow(ValidationException::class);
        expect(Reservation::activeAt(now())->count())->toBe(1)
            ->and($reservation->events()->count())->toBe(1);
    } finally {
        $writer = null;
        DB::purge('race_service');
        config(['database.default' => $original]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        unlink($path);
    }
});
