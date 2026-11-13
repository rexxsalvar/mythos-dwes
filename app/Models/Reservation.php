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

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ['rate_id', 'booking_policy_version_id', 'booked_by', 'contact_name', 'contact_email', 'contact_phone', 'locale', 'checked_in_at', 'checked_in_by', 'cancelled_by', 'cancellation_reason', 'notes', 'reference', 'user_id', 'game_session_id', 'team_id', 'players', 'total_cents', 'currency', 'status', 'expires_at', 'confirmed_at', 'cancelled_at', 'needs_review'];

    #[Scope]
    protected function upcoming(
        Builder $query,
        ?DateTimeInterface $instant = null,
        ?DateTimeInterface $until = null,
        bool $affectedOnly = false,
    ): void {
        $instant = CarbonImmutable::instance($instant ?? now())->utc();
        $until = $until === null ? null : CarbonImmutable::instance($until)->utc();

        $query->when($affectedOnly, fn (Builder $reservations) => $reservations->where('reservations.status', 'confirmed'))
            ->whereHas('gameSession', function (Builder $session) use ($instant, $until, $affectedOnly) {
                $session->where('game_sessions.starts_at', '>=', $instant)
                    ->when($until !== null, fn (Builder $period) => $period->where('game_sessions.starts_at', '<', $until));

                if ($affectedOnly) {
                    $session->where(function (Builder $affected) {
                        $affected->whereHas('room.incidents', fn (Builder $incidents) => $incidents->requiringAttention(severity: 'critical'))
                            ->orWhereExists(function (QueryBuilder $blocks) {
                                $blocks->selectRaw('1')->from('availability_blocks')
                                    ->whereNull('released_at')
                                    ->where(fn (QueryBuilder $resources) => $resources
                                        ->whereColumn('room_id', 'game_sessions.room_id')
                                        ->orWhereColumn('user_id', 'game_sessions.game_master_id'))
                                    ->whereColumn('starts_at', '<', 'game_sessions.ends_at')
                                    ->where(fn (QueryBuilder $ends) => $ends->whereNull('ends_at')
                                        ->orWhereRaw("availability_blocks.ends_at > datetime(game_sessions.starts_at, '-' || coalesce(game_sessions.preparation_minutes, 0) || ' minutes')"));
                            });
                    });
                }
            });
    }

    #[Scope]
    protected function activeAt(Builder $query, DateTimeInterface $instant): void
    {
        $instant = CarbonImmutable::instance($instant)->utc();

        $query->where(function (Builder $active) use ($instant) {
            $active->where('reservations.status', 'confirmed')
                ->orWhere(function (Builder $pending) use ($instant) {
                    // Older pending reservations may have no recorded deadline.
                    $pending->where('reservations.status', 'pending')
                        ->where(fn (Builder $expiry) => $expiry
                            ->whereNull('reservations.expires_at')
                            ->orWhere('reservations.expires_at', '>', $instant));
                });
        });
    }

    protected function casts(): array
    {
        return ['checked_in_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'confirmed_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
            'needs_review' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class);
    }

    public function bookingPolicyVersion(): BelongsTo
    {
        return $this->belongsTo(BookingPolicyVersion::class);
    }

    public function booker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by');
    }

    public function checkInOperator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ReservationParticipant::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ReservationEvent::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function incidentImpacts(): HasMany
    {
        return $this->hasMany(IncidentImpact::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GeneratedDocument::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    public function voucherRedemptions(): HasMany
    {
        return $this->hasMany(GiftVoucherRedemption::class);
    }

    public function incidents(): BelongsToMany
    {
        return $this->belongsToMany(Incident::class, 'reservation_incident')->using(IncidentImpact::class)
            ->withPivot(['id', 'detected_at', 'status', 'reviewed_by', 'reviewed_at', 'resolution_note'])->withTimestamps();
    }
}
