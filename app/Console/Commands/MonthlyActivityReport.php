<?php

namespace App\Console\Commands;

use App\Services\ActivityReportService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use InvalidArgumentException;

class MonthlyActivityReport extends Command
{
    protected $signature = 'reports:monthly {month? : Mes en formato YYYY-MM} {--room= : Identificador de sala}';

    protected $description = 'Consultar el resumen mensual de sesiones y reservas en JSON';

    public function handle(ActivityReportService $reports): int
    {
        $room = $this->option('room');
        $roomId = $room === null ? null : filter_var($room, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($roomId === false) {
            $this->error('La opción --room debe ser un entero positivo.');

            return self::INVALID;
        }

        try {
            $month = $this->argument('month') ?? CarbonImmutable::now('Europe/Madrid')->format('Y-m');
            $summary = $reports->monthly($month, $roomId);
            $this->line(json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::INVALID;
        } catch (QueryException $exception) {
            report($exception);
            $this->error('No se pudo consultar la actividad mensual. Revisa la conexión de la base de datos.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
