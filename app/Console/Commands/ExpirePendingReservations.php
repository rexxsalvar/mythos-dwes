<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Command;
use PDOException;

class ExpirePendingReservations extends Command
{
    protected $signature = 'reservations:expire';

    protected $description = 'Caduca reservas pendientes cuyo bloqueo ha vencido y conserva su historial';

    public function handle(ReservationService $service): int
    {
        try {
            $count = $service->expirePending();
        } catch (PDOException $exception) {
            report($exception);
            $this->error('No se pudo completar la caducidad. Vuelve a intentarlo.');

            return self::FAILURE;
        }

        $this->info('Reservas caducadas: '.$count);

        return self::SUCCESS;
    }
}
