<?php

namespace App\Services;

use App\Models\AvailabilityBlock;
use App\Models\GameSession;
use App\Models\RoomInstallation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use PDOException;

class ScheduleSession
{
    private const PREPARATION_MINUTES = 30;

    public function create(
        User $actor,
        RoomInstallation $installation,
        User $gameMaster,
        CarbonInterface $startsAt,
        CarbonInterface $endsAt,
        string $locale = 'es',
    ): GameSession {
        return $this->write(function () use ($actor, $installation, $gameMaster, $startsAt, $endsAt, $locale) {
            Gate::forUser($actor)->authorize('create', GameSession::class);

            return GameSession::create([
                ...$this->scheduleAttributes($installation, $gameMaster, $startsAt, $endsAt, $locale),
                'status' => 'scheduled',
            ]);
        });
    }

    public function update(
        User $actor,
        GameSession $session,
        RoomInstallation $installation,
        User $gameMaster,
        CarbonInterface $startsAt,
        CarbonInterface $endsAt,
        string $locale = 'es',
    ): GameSession {
        return $this->write(function () use ($actor, $session, $installation, $gameMaster, $startsAt, $endsAt, $locale) {
            $current = $this->authorizeChange($actor, $session, 'update');
            if ($current->reservations()->exists()) {
                throw ValidationException::withMessages(['session' => 'La sesión ya tiene historial de reservas. Crea otra sesión para cambiar su horario.']);
            }

            $attributes = $this->scheduleAttributes($installation, $gameMaster, $startsAt, $endsAt, $locale);
            if ($current->experience_id !== $attributes['experience_id']) {
                $attributes['booking_policy_version_id'] = null;
                $attributes['preparation_template_id'] = null;
            }

            $current->update($attributes);

            return $current;
        });
    }

    public function cancel(User $actor, GameSession $session): GameSession
    {
        return $this->write(function () use ($actor, $session) {
            $current = $this->authorizeChange($actor, $session, 'delete');
            if ($current->reservations()->activeAt(now())->exists()) {
                throw ValidationException::withMessages(['session' => 'Gestiona la reserva activa antes de cancelar la sesión.']);
            }

            $current->update(['status' => 'cancelled']);

            return $current;
        });
    }

    private function authorizeChange(User $actor, GameSession $session, string $ability): GameSession
    {
        $current = GameSession::findOrFail($session->getKey());
        Gate::forUser($actor)->authorize($ability, $current);

        $preparationStartsAt = $current->starts_at->subMinutes($current->preparation_minutes ?? 0);
        if ($preparationStartsAt->lte(now())) {
            throw ValidationException::withMessages(['session' => 'La preparación de la sesión ya ha comenzado.']);
        }

        return $current;
    }

    private function scheduleAttributes(
        RoomInstallation $installation,
        User $gameMaster,
        CarbonInterface $startsAt,
        CarbonInterface $endsAt,
        string $locale,
    ): array {
        $installation = RoomInstallation::with('room.venue', 'experience.languages')->findOrFail($installation->getKey());
        $room = $installation->room;
        $experience = $installation->experience;
        $gameMaster = User::findOrFail($gameMaster->getKey());
        $startsAt = CarbonImmutable::instance($startsAt)->utc()->startOfSecond();
        $endsAt = CarbonImmutable::instance($endsAt)->utc()->startOfSecond();
        $occupiedFrom = $startsAt->subMinutes(self::PREPARATION_MINUTES);

        if ($occupiedFrom->lte(now())) {
            throw ValidationException::withMessages(['starts_at' => 'La preparación de la sesión debe comenzar en el futuro.']);
        }
        if ($endsAt->lte($startsAt)) {
            throw ValidationException::withMessages(['ends_at' => 'La sesión debe terminar después de su inicio.']);
        }
        if ($room->status !== 'operational' || $room->archived_at !== null || $room->venue?->is_active === false ||
            ! $experience->is_active || $experience->archived_at !== null ||
            $installation->installed_from->gt($startsAt) || $installation->installed_until?->lt($endsAt)) {
            throw ValidationException::withMessages(['room_experience_id' => 'La instalación debe estar disponible durante toda la sesión.']);
        }
        if (! $gameMaster->is_active || ! $gameMaster->hasRole('game_master')) {
            throw ValidationException::withMessages(['game_master_id' => 'Selecciona un game master activo.']);
        }

        $languages = $experience->languages->pluck('locale')->all() ?: ['es'];
        if (! in_array($locale, $languages, true)) {
            throw ValidationException::withMessages(['locale' => 'Este idioma no está disponible para la experiencia.']);
        }

        if (AvailabilityBlock::query()->whereNull('released_at')
            ->where(fn (Builder $blocks) => $blocks->where('room_id', $room->id)->orWhere('user_id', $gameMaster->id))
            ->where('starts_at', '<', $endsAt)
            ->where(fn (Builder $blocks) => $blocks->whereNull('ends_at')->orWhere('ends_at', '>', $occupiedFrom))
            ->exists()
            || $room->incidents()->where('severity', 'critical')->whereIn('status', ['open', 'in_progress'])->exists()) {
            throw ValidationException::withMessages(['starts_at' => 'La sala o el game master tienen un bloqueo durante la sesión o su preparación.']);
        }

        return [
            'room_experience_id' => $installation->id,
            'room_id' => $room->id,
            'experience_id' => $experience->id,
            'game_master_id' => $gameMaster->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'preparation_minutes' => self::PREPARATION_MINUTES,
            'locale' => $locale,
        ];
    }

    private function write(Closure $operation): GameSession
    {
        try {
            return DB::transaction($operation);
        } catch (PDOException $exception) {
            if (in_array(((int) ($exception->errorInfo[1] ?? 0)) & 255, [5, 6], true)) {
                throw ValidationException::withMessages(['starts_at' => 'La agenda está siendo actualizada. Consulta la disponibilidad y vuelve a intentarlo.']);
            }
            // SQLite's overlap triggers also protect writers that bypass this service.
            if (str_contains($exception->getMessage(), 'Session overlaps room or game master')) {
                throw ValidationException::withMessages(['starts_at' => 'La sesión o su preparación se solapan con la agenda de la sala o del game master.']);
            }

            throw $exception;
        }
    }
}
