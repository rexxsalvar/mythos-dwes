<?php

// Read-only inventory to inspect the database built by the migrations.
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$tables = [];
foreach (DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name") as $table) {
    $name = $table->name;
    $tables[] = ['table' => $name, 'rows' => DB::table($name)->count(), 'columns' => array_map(fn ($column) => $column->name, DB::select('PRAGMA table_info("'.str_replace('"', '""', $name).'")'))];
}
echo json_encode(['table_count' => count($tables), 'foreign_key_violations' => count(DB::select('PRAGMA foreign_key_check')), 'tables' => $tables], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
