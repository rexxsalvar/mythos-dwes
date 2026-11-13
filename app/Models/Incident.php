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

class Incident extends Model
{
    use HasFactory;

    protected $fillable = ['room_id', 'game_session_id', 'reported_by', 'assigned_to', 'title', 'description', 'severity', 'status', 'resolved_at'];

    #[Scope]
    protected function requiringAttention(
        Builder $query,
        ?int $roomId = null,
        ?string $severity = null,
        ?DateTimeInterface $reservationsAfter = null,
    ): void {
        $query->whereIn('incidents.status', ['open', 'in_progress'])
            ->whereIn('incidents.severity', $severity === null ? ['high', 'critical'] : [$severity])
            ->when($roomId !== null, fn (Builder $incidents) => $incidents
                ->whereHas('room', fn (Builder $room) => $room->whereKey($roomId)))
            ->when($reservationsAfter !== null, fn (Builder $incidents) => $incidents
                ->whereHas('room.gameSessions', fn (Builder $sessions) => $sessions
                    ->where('starts_at', '>=', CarbonImmutable::instance($reservationsAfter)->utc())
                    ->whereHas('reservations', fn (Builder $reservations) => $reservations->where('status', 'confirmed'))));
    }

    protected function casts(): array
    {
        return [
            'resolved_at' => 'immutable_datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function maintenanceActions(): HasMany
    {
        return $this->hasMany(MaintenanceAction::class);
    }

    public function availabilityBlocks(): HasMany
    {
        return $this->hasMany(AvailabilityBlock::class);
    }

    public function reservationImpacts(): HasMany
    {
        return $this->hasMany(IncidentImpact::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'reservation_incident')->using(IncidentImpact::class)
            ->withPivot(['id', 'detected_at', 'status', 'reviewed_by', 'reviewed_at', 'resolution_note'])->withTimestamps();
    }
}
