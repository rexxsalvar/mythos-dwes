<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;

class GameSession extends Model
{
    use HasFactory;

    protected $fillable = ['room_experience_id', 'booking_policy_version_id', 'preparation_template_id', 'locale', 'actual_started_at', 'actual_ended_at', 'experience_id', 'room_id', 'game_master_id', 'starts_at', 'ends_at', 'status', 'preparation_minutes'];

    #[Scope]
    protected function availableBetween(
        Builder $query,
        DateTimeInterface $from,
        DateTimeInterface $until,
        int $experienceId,
        int $players,
        ?DateTimeInterface $instant = null,
        ?int $roomId = null,
        ?int $gameMasterId = null,
        ?int $exceptReservationId = null,
    ): void {
        $instant = CarbonImmutable::instance($instant ?? now())->utc();
        $from = CarbonImmutable::instance($from)->utc();
        $until = CarbonImmutable::instance($until)->utc();
        $preparationStart = "datetime(game_sessions.starts_at, '-' || coalesce(game_sessions.preparation_minutes, 0) || ' minutes')";

        $query->where('game_sessions.status', 'scheduled')
            ->where('game_sessions.experience_id', $experienceId)
            ->where('game_sessions.starts_at', '>=', $from)
            ->where('game_sessions.starts_at', '<', $until)
            ->whereRaw("$preparationStart > ?", [$instant])
            ->when($roomId !== null, fn (Builder $sessions) => $sessions->where('game_sessions.room_id', $roomId))
            ->when($gameMasterId !== null, fn (Builder $sessions) => $sessions->where('game_sessions.game_master_id', $gameMasterId))
            ->whereHas('experience', fn (Builder $experience) => $experience->published()
                ->where('min_players', '<=', $players)
                ->where('max_players', '>=', $players))
            ->whereHas('gameMaster', fn (Builder $gameMaster) => $gameMaster->where('is_active', true))
            ->whereHas('installation', fn (Builder $installation) => $installation
                ->whereColumn('installed_from', '<=', 'game_sessions.starts_at')
                ->where(fn (Builder $end) => $end->whereNull('installed_until')
                    ->orWhereColumn('installed_until', '>=', 'game_sessions.ends_at')))
            ->whereHas('room', fn (Builder $room) => $room->where('status', 'operational')
                ->whereNull('archived_at')
                ->where('capacity', '>=', $players)
                ->where(fn (Builder $venue) => $venue->whereNull('location_id')
                    ->orWhereHas('venue', fn (Builder $location) => $location->where('is_active', true)))
                ->whereDoesntHave('incidents', fn (Builder $incident) => $incident->requiringAttention(severity: 'critical')))
            ->whereDoesntHave('reservations', fn (Builder $reservations) => $reservations
                ->activeAt($instant)
                ->when($exceptReservationId !== null, fn (Builder $active) => $active->whereKeyNot($exceptReservationId)))
            ->whereNotExists(function (QueryBuilder $blocks) use ($preparationStart) {
                $blocks->selectRaw('1')->from('availability_blocks')
                    ->whereNull('released_at')
                    ->where(fn (QueryBuilder $resources) => $resources
                        ->whereColumn('room_id', 'game_sessions.room_id')
                        ->orWhereColumn('user_id', 'game_sessions.game_master_id'))
                    ->whereColumn('starts_at', '<', 'game_sessions.ends_at')
                    ->where(fn (QueryBuilder $ends) => $ends->whereNull('ends_at')
                        ->orWhereRaw("availability_blocks.ends_at > $preparationStart"));
            });
    }

    protected function casts(): array
    {
        return ['actual_started_at' => 'immutable_datetime', 'actual_ended_at' => 'immutable_datetime',
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
        ];
    }

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function gameMaster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'game_master_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function preparationChecks(): HasMany
    {
        return $this->hasMany(PreparationCheck::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(GameResult::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function hints(): BelongsToMany
    {
        return $this->belongsToMany(Hint::class)->using(HintUsage::class)
            ->withPivot(['used_at', 'penalty_seconds'])->withTimestamps();
    }

    public function installation(): BelongsTo
    {
        return $this->belongsTo(RoomInstallation::class, 'room_experience_id');
    }

    public function bookingPolicyVersion(): BelongsTo
    {
        return $this->belongsTo(BookingPolicyVersion::class);
    }

    public function preparationTemplate(): BelongsTo
    {
        return $this->belongsTo(PreparationTemplate::class);
    }
}
