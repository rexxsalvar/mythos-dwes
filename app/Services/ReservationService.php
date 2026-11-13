<?php

namespace App\Services;

use App\Models\BookingPolicyVersion;
use App\Models\GameSession;
use App\Models\Rate;
use App\Models\Reservation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PDOException;

class ReservationService
{
    private const HOLD_MINUTES = 15;

    private const CANCELLATION_NOTICE_MINUTES = 1440;

    public function hold(User $actor, GameSession $session, int $players, ?User $customer = null): Reservation
    {
        $customer ??= $actor;

        return $this->write(function () use ($actor, $session, $players, $customer) {
            $customer = User::findOrFail($customer->id);
            Gate::forUser($actor)->authorize('createFor', [Reservation::class, $customer]);
            $session = GameSession::findOrFail($session->id);
            $now = now()->toImmutable()->utc();
            $preparationStarts = $this->preparationStarts($session);

            if ($now->gte($preparationStarts)) {
                throw ValidationException::withMessages(['session' => 'Ya ha comenzado el periodo de preparación de la sesión.']);
            }

            if ($players < $session->experience->min_players || $players > min($session->experience->max_players, $session->room->capacity)) {
                throw ValidationException::withMessages(['players' => 'El grupo debe respetar el aforo de la experiencia y de la sala.']);
            }

            $this->expirePending($session->id);

            $this->ensureAvailable($session, $players, $now);

            $rate = Rate::effectiveForGroup($session->experience_id, $players, 'EUR', $now)->first();
            if (! $rate) {
                throw ValidationException::withMessages(['players' => 'No hay una tarifa vigente para este grupo.']);
            }

            $reservation = $session->reservations()->create([
                'reference' => (string) Str::uuid(),
                'user_id' => $customer->id,
                'booked_by' => $actor->id,
                'players' => $players,
                'rate_id' => $rate->id,
                'total_cents' => $rate->total_cents,
                'currency' => $rate->currency,
                'booking_policy_version_id' => $this->bookingPolicy($session, $now)->id,
                'contact_name' => $customer->name,
                'contact_email' => $customer->email,
                'contact_phone' => $customer->phone,
                'locale' => $customer->preferred_locale,
                'status' => 'pending',
                'expires_at' => $now->addMinutes(self::HOLD_MINUTES)->min($preparationStarts),
            ]);
            $reservation->events()->create([
                'actor_id' => $actor->id,
                'action' => 'created',
                'occurred_at' => $now,
                'after_values' => $this->snapshot($reservation),
            ]);

            return $reservation;
        });
    }

    public function confirm(User $actor, Reservation $reservation): Reservation
    {
        $result = $this->write(function () use ($actor, $reservation) {
            $reservation = Reservation::findOrFail($reservation->id);
            Gate::forUser($actor)->authorize('confirm', $reservation);
            $now = now()->toImmutable()->utc();

            if ($reservation->expires_at === null) {
                throw ValidationException::withMessages(['reservation' => 'La reserva no tiene un bloqueo vigente.']);
            }
            if ($reservation->expires_at->lte($now)) {
                return $this->transition($reservation, 'expired', $now);
            }

            $this->ensureAvailable($reservation->gameSession, $reservation->players, $now, $reservation->id);

            return $this->transition($reservation, 'confirmed', $now, $actor, ['confirmed_at' => $now]);
        });

        if ($result->status === 'expired') {
            throw ValidationException::withMessages(['reservation' => 'El plazo de esta reserva pendiente ha vencido.']);
        }

        return $result;
    }

    public function expirePending(?int $sessionId = null): int
    {
        return DB::transaction(function () use ($sessionId) {
            $now = now()->toImmutable()->utc();
            $expired = Reservation::query()->where('status', 'pending')->where('expires_at', '<=', $now)
                ->when($sessionId !== null, fn ($query) => $query->where('game_session_id', $sessionId))->get();
            foreach ($expired as $reservation) {
                $this->transition($reservation, 'expired', $now);
            }

            return $expired->count();
        });
    }

    public function changeGroup(User $actor, Reservation $reservation, int $players): Reservation
    {
        return $this->write(function () use ($actor, $reservation, $players) {
            $reservation = Reservation::findOrFail($reservation->id);
            Gate::forUser($actor)->authorize('update', $reservation);
            $now = now()->toImmutable()->utc();
            if ($reservation->expires_at === null || $reservation->expires_at->lte($now)) {
                throw ValidationException::withMessages(['reservation' => 'La reserva no tiene un bloqueo vigente.']);
            }
            if ($reservation->participants()->count() > $players) {
                throw ValidationException::withMessages(['players' => 'El grupo debe incluir a los participantes ya registrados.']);
            }
            $this->ensureAvailable($reservation->gameSession, $players, $now, $reservation->id);
            $rate = Rate::effectiveForGroup($reservation->gameSession->experience_id, $players, $reservation->currency, $now)->first();
            if (! $rate) {
                throw ValidationException::withMessages(['players' => 'No hay una tarifa vigente para este grupo.']);
            }

            return $this->transition($reservation, 'pending', $now, $actor, [
                'players' => $players, 'rate_id' => $rate->id, 'total_cents' => $rate->total_cents,
            ]);
        });
    }

    public function cancel(User $actor, Reservation $reservation, ?string $reason = null): Reservation
    {
        return $this->write(function () use ($actor, $reservation, $reason) {
            $reservation = Reservation::findOrFail($reservation->id);
            Gate::forUser($actor)->authorize('cancel', $reservation);
            $now = now()->toImmutable()->utc();

            if (! $this->canCancel($reservation, $now)) {
                throw ValidationException::withMessages(['reservation' => 'La reserva no tiene un plazo de cancelación vigente.']);
            }

            return $this->transition($reservation, 'cancelled', $now, $actor, [
                'cancelled_at' => $now,
                'cancelled_by' => $actor->id,
                'cancellation_reason' => $reason,
            ]);
        });
    }

    private function canCancel(Reservation $reservation, CarbonImmutable $now): bool
    {
        if ($reservation->status === 'pending') {
            return $reservation->expires_at === null || $reservation->expires_at->gt($now);
        }

        $notice = $reservation->bookingPolicyVersion?->cancellation_notice_minutes;

        return $reservation->status === 'confirmed' && $notice !== null
            && $reservation->gameSession->starts_at->gt($now)
            && $now->lte($reservation->gameSession->starts_at->subMinutes((int) $notice));
    }

    private function preparationStarts(GameSession $session): CarbonImmutable
    {
        return $session->starts_at->subMinutes((int) $session->preparation_minutes);
    }

    private function bookingPolicy(GameSession $session, CarbonImmutable $now): BookingPolicyVersion
    {
        $policies = $session->experience->bookingPolicies();
        $available = $policies->where('status', 'active')->whereNotNull('cancellation_notice_minutes')
            ->where(fn ($query) => $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now))
            ->where(fn ($query) => $query->whereNull('valid_until')->orWhere('valid_until', '>', $now));
        $assigned = (clone $available)->whereKey($session->booking_policy_version_id)->first();
        if ($policy = $assigned ?? $available->orderByDesc('version')->first()) {
            return $policy;
        }

        // This runs inside the same IMMEDIATE transaction as the hold. Drafts and
        // conditions referenced by earlier reservations retain their original values.
        return $session->experience->bookingPolicies()->create([
            'version' => 1 + (int) $session->experience->bookingPolicies()->max('version'),
            'name' => 'Condiciones académicas S4',
            'status' => 'active', 'valid_from' => $now,
            'cancellation_notice_minutes' => self::CANCELLATION_NOTICE_MINUTES,
            'pending_expiry_minutes' => self::HOLD_MINUTES, 'preparation_minutes' => 30,
        ]);
    }

    private function ensureAvailable(GameSession $session, int $players, CarbonImmutable $now, ?int $ignoreReservation = null): void
    {
        if (! GameSession::query()->whereKey($session->id)->availableBetween(
            $session->starts_at,
            $session->starts_at->addSecond(),
            $session->experience_id,
            $players,
            $now,
            exceptReservationId: $ignoreReservation,
        )->exists()) {
            throw ValidationException::withMessages(['session' => 'La sesión ya no está disponible para este grupo.']);
        }
    }

    private function transition(
        Reservation $reservation,
        string $status,
        CarbonImmutable $now,
        ?User $actor = null,
        array $changes = [],
    ): Reservation {
        $before = $this->snapshot($reservation);
        $reservation->update(['status' => $status] + $changes);
        $reservation->events()->create([
            'actor_id' => $actor?->id,
            'action' => $status,
            'occurred_at' => $now,
            'reason' => $changes['cancellation_reason'] ?? null,
            'before_values' => $before,
            'after_values' => $this->snapshot($reservation),
        ]);

        return $reservation;
    }

    private function snapshot(Reservation $reservation): array
    {
        return $reservation->only([
            'status', 'players', 'rate_id', 'total_cents', 'currency', 'booking_policy_version_id', 'expires_at', 'confirmed_at', 'cancelled_at', 'cancelled_by',
        ]);
    }

    private function write(Closure $operation): Reservation
    {
        try {
            return DB::transaction($operation);
        } catch (PDOException $error) {
            $sqliteCode = ((int) ($error->errorInfo[1] ?? 0)) & 255;
            if (in_array($sqliteCode, [5, 6], true)
                || str_contains($error->getMessage(), 'UNIQUE constraint failed: reservations.game_session_id')) {
                throw ValidationException::withMessages(['session' => 'No se pudo reservar o actualizar la sesión. Comprueba su disponibilidad de nuevo.']);
            }

            throw $error;
        }
    }
}
