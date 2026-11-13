<?php

namespace App\Console\Commands;

use App\Services\ReviewAffectedReservationsService;
use Illuminate\Console\Command;
use PDOException;

class ReviewAffectedReservations extends Command
{
    protected $signature = 'incidents:review-reservations';

    protected $description = 'Marca reservas confirmadas futuras afectadas por incidencias críticas o bloqueos';

    public function handle(ReviewAffectedReservationsService $service): int
    {
        try {
            $result = $service->review();
        } catch (PDOException $exception) {
            report($exception);
            $this->error('No se pudo completar la revisión de reservas. Vuelve a intentarlo.');

            return self::FAILURE;
        }

        $this->info('Reservas afectadas: '.$result['affected'].'. Nuevas marcas de revisión: '.$result['flagged'].'. Incidencias vinculadas: '.$result['incidents_linked'].'.');

        return self::SUCCESS;
    }
}
