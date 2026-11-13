<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentImpact;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class ReviewAffectedReservationsService
{
    public function review(): array
    {
        return DB::transaction(function () {
            $instant = now()->toImmutable()->utc();
            $incidents = Incident::requiringAttention(severity: 'critical', reservationsAfter: $instant)
                ->get()->groupBy('room_id');
            $reservations = Reservation::upcoming($instant, affectedOnly: true)->with('gameSession')->get();
            $flagged = $linked = 0;

            foreach ($reservations as $reservation) {
                if (! $reservation->needs_review) {
                    $reservation->update(['needs_review' => true]);
                    $reservation->events()->create([
                        'action' => 'review_required', 'occurred_at' => $instant,
                        'reason' => 'Incidencia crítica o bloqueo que afecta a la sesión o su preparación.',
                        'before_values' => ['needs_review' => false],
                        'after_values' => ['needs_review' => true],
                    ]);
                    $flagged++;
                }

                foreach ($incidents->get($reservation->gameSession->room_id, collect()) as $incident) {
                    $impact = IncidentImpact::firstOrCreate([
                        'reservation_id' => $reservation->id, 'incident_id' => $incident->id,
                    ], ['detected_at' => $instant, 'status' => 'pending']);
                    if ($impact->wasRecentlyCreated) {
                        $linked++;
                    }
                }
            }

            return ['affected' => $reservations->count(), 'flagged' => $flagged, 'incidents_linked' => $linked];
        });
    }
}
