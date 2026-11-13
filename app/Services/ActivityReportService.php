<?php

namespace App\Services;

use App\Models\GameSession;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Query\JoinClause;
use InvalidArgumentException;

class ActivityReportService
{
    public function monthly(string $month, ?int $roomId = null): array
    {
        $start = $this->periodStart($month, 'Y-m', '/^\d{4}-\d{2}$/D');

        return $this->summarize('monthly', $start, $start->addMonth(), $roomId);
    }

    public function daily(string $date, ?int $roomId = null): array
    {
        $start = $this->periodStart($date, 'Y-m-d', '/^\d{4}-\d{2}-\d{2}$/D');

        return $this->summarize('daily', $start, $start->addDay(), $roomId);
    }

    private function periodStart(string $value, string $format, string $pattern): CarbonImmutable
    {
        if (! preg_match($pattern, $value)) {
            throw new InvalidArgumentException("El periodo debe usar el formato {$format}.");
        }

        try {
            $date = CarbonImmutable::createFromFormat('!'.$format, $value, 'Europe/Madrid');
        } catch (InvalidFormatException $exception) {
            throw new InvalidArgumentException('El periodo indicado no es válido.', previous: $exception);
        }

        if (! $date || $date->format($format) !== $value || $date->year < 1) {
            throw new InvalidArgumentException('El periodo indicado no es válido.');
        }

        return $date;
    }

    private function summarize(string $kind, CarbonImmutable $from, CarbonImmutable $until, ?int $roomId): array
    {
        if ($roomId !== null && ($roomId < 1 || ! Room::whereKey($roomId)->exists())) {
            throw new InvalidArgumentException('La sala indicada no existe.');
        }

        // Only this one-to-many relation participates. Participants and payments cannot multiply rows.
        $rows = GameSession::query()
            ->leftJoin('reservations', function (JoinClause $join) {
                $join->on('reservations.game_session_id', '=', 'game_sessions.id')
                    ->whereIn('reservations.status', ['confirmed', 'completed']);
            })
            ->where('game_sessions.starts_at', '>=', $from->utc())
            ->where('game_sessions.starts_at', '<', $until->utc())
            ->when($roomId !== null, fn ($query) => $query->where('game_sessions.room_id', $roomId))
            ->orderBy('game_sessions.id')
            ->get([
                'game_sessions.id', 'game_sessions.game_master_id', 'game_sessions.starts_at',
                'game_sessions.ends_at', 'game_sessions.status', 'game_sessions.preparation_minutes',
                'reservations.id as reservation_id', 'reservations.players', 'reservations.total_cents',
                'reservations.currency',
            ]);

        $statuses = array_fill_keys(['scheduled', 'preparing', 'in_progress', 'completed', 'cancelled'], 0);
        $seenSessions = [];
        $occupiedSessions = [];
        $currencyTotals = [];
        $workload = [];

        foreach ($rows as $row) {
            if (! isset($seenSessions[$row->id])) {
                $seenSessions[$row->id] = true;
                $statuses[$row->status]++;

                if ($row->status !== 'cancelled') {
                    $masterId = $row->game_master_id;
                    $workload[$masterId] ??= [
                        'game_master_id' => $masterId,
                        'session_count' => 0,
                        'game_minutes' => 0,
                        'recorded_preparation_minutes' => 0,
                        'sessions_without_preparation_minutes' => 0,
                    ];
                    $workload[$masterId]['session_count']++;
                    $workload[$masterId]['game_minutes'] += (int) $row->starts_at->diffInMinutes($row->ends_at);
                    $workload[$masterId]['recorded_preparation_minutes'] += $row->preparation_minutes ?? 0;
                    $workload[$masterId]['sessions_without_preparation_minutes'] += $row->preparation_minutes === null ? 1 : 0;
                }
            }

            if ($row->reservation_id !== null) {
                $occupiedSessions[$row->id] = true;
                $currencyTotals[$row->currency] ??= ['reservation_count' => 0, 'players' => 0, 'total_cents' => 0];
                $currencyTotals[$row->currency]['reservation_count']++;
                $currencyTotals[$row->currency]['players'] += (int) $row->players;
                $currencyTotals[$row->currency]['total_cents'] += (int) $row->total_cents;
            }
        }

        ksort($currencyTotals);
        ksort($workload);

        return [
            'kind' => $kind,
            'timezone' => 'Europe/Madrid',
            'from' => $from->toDateString(),
            'until_exclusive' => $until->toDateString(),
            'from_utc' => $from->utc()->toIso8601String(),
            'until_utc_exclusive' => $until->utc()->toIso8601String(),
            'filters' => ['room_id' => $roomId],
            'session_count' => count($seenSessions),
            'sessions_by_status' => $statuses,
            'sessions_with_confirmed_or_completed_reservation' => count($occupiedSessions),
            'confirmed_or_completed_reservations_by_currency' => $currencyTotals,
            'programmed_workload_by_game_master' => array_values($workload),
        ];
    }
}
