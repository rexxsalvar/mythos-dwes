<?php

// Exports structure only. No people, passwords, tokens or other row values are included.
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$sql = "-- Mythos: esquema SQLite. Generado desde las migraciones; sin datos.\nPRAGMA foreign_keys = ON;\nBEGIN TRANSACTION;\n\n";
foreach (['table', 'index', 'trigger'] as $type) {
    foreach (DB::select("SELECT name, sql FROM sqlite_master WHERE type = ? AND name NOT LIKE 'sqlite_%' AND sql IS NOT NULL ORDER BY name", [$type]) as $object) {
        $sql .= $object->sql.";\n\n";
    }
}
$sql .= "COMMIT;\n";
$path = __DIR__.'/../docs/base-datos.sql';
file_put_contents($path, $sql);
echo 'Esquema exportado a docs/base-datos.sql (sin datos).'.PHP_EOL;
