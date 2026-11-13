<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = ['rate_plan_id', 'experience_id', 'players', 'total_cents', 'currency'];

    #[Scope]
    protected function effectiveForGroup(Builder $query, int $experienceId, int $players, string $currency, DateTimeInterface $instant): void
    {
        $instant = CarbonImmutable::instance($instant)->utc();

        $query->where('rates.experience_id', $experienceId)
            ->where('rates.players', $players)
            ->where('rates.currency', $currency)
            ->whereHas('ratePlan', fn (Builder $plan) => $plan->where('status', 'active')
                ->where('valid_from', '<=', $instant)
                ->where(fn (Builder $end) => $end->whereNull('valid_until')->orWhere('valid_until', '>', $instant)));
    }

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
