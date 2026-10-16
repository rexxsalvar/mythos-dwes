<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

test('migrations reverse and rebuild on a separate disposable database', function () {
    $path = tempnam(sys_get_temp_dir(), 'mythos-test-');
    config(['database.connections.rebuild' => array_replace(config('database.connections.sqlite'), ['database' => $path])]);
    try {
        expect(Artisan::call('migrate', ['--database' => 'rebuild', '--force' => true]))->toBe(0);
        expect(DB::connection('rebuild')->getSchemaBuilder()->hasTable('reservations'))->toBeTrue();
        expect(Artisan::call('migrate:rollback', ['--database' => 'rebuild', '--force' => true]))->toBe(0);
        expect(DB::connection('rebuild')->getSchemaBuilder()->hasTable('experiences'))->toBeFalse();
        expect(Artisan::call('migrate', ['--database' => 'rebuild', '--force' => true]))->toBe(0);
        expect(DB::connection('rebuild')->getSchemaBuilder()->hasTable('reservations'))->toBeTrue();
    } finally {
        DB::purge('rebuild');
        unlink($path);
    }
});

test('two independent SQLite writers cannot commit competing active reservations', function () {
    $path = tempnam(sys_get_temp_dir(), 'mythos-race-');
    config(['database.connections.race' => array_replace(config('database.connections.sqlite'), ['database' => $path])]);
    $first = $second = null;
    try {
        expect(Artisan::call('migrate', ['--database' => 'race', '--force' => true]))->toBe(0);
        $db = DB::connection('race');
        $user = $db->table('users')->insertGetId(['name' => 'Demo', 'email' => 'race@example.test', 'password' => 'not-a-login']);
        $experience = $db->table('experiences')->insertGetId(['name' => 'Demo', 'slug' => 'race', 'description' => 'Test', 'duration_minutes' => 75, 'min_players' => 2, 'max_players' => 6]);
        $room = $db->table('rooms')->insertGetId(['experience_id' => $experience, 'name' => 'Demo', 'location' => 'Ficticia', 'capacity' => 6, 'status' => 'operational']);
        $session = $db->table('game_sessions')->insertGetId(['experience_id' => $experience, 'room_id' => $room, 'game_master_id' => $user, 'starts_at' => '2026-10-17 10:00:00', 'ends_at' => '2026-10-17 11:15:00', 'status' => 'scheduled']);
        $first = new PDO('sqlite:'.$path, options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $second = new PDO('sqlite:'.$path, options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        foreach ([$first, $second] as $writer) {
            $writer->exec('PRAGMA foreign_keys = ON; PRAGMA busy_timeout = 1;');
        }
        unset($writer);
        $sql = "INSERT INTO reservations(reference,user_id,game_session_id,players,total_cents,currency,status) VALUES(?,?,?,2,8000,'EUR','confirmed')";
        $first->beginTransaction();
        $first->prepare($sql)->execute(['writer-a', $user, $session]);
        expect(fn () => $second->prepare($sql)->execute(['writer-b', $user, $session]))->toThrow(PDOException::class, 'locked');
        $first->commit();
        expect(fn () => $second->prepare($sql)->execute(['writer-b', $user, $session]))->toThrow(PDOException::class, 'UNIQUE constraint failed');
        expect((int) $second->query('SELECT COUNT(*) FROM reservations')->fetchColumn())->toBe(1);
    } finally {
        if ($first?->inTransaction()) {
            $first->rollBack();
        }
        $first = $second = null;
        DB::purge('race');
        unlink($path);
    }
});
