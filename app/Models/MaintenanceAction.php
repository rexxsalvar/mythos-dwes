<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceAction extends Model
{
    use HasFactory;

    protected $fillable = ['incident_id', 'technician_id', 'description', 'performed_at'];

    #[Scope]
    protected function performedBetween(Builder $query, DateTimeInterface $from, DateTimeInterface $until, ?int $technicianId = null): void
    {
        $query->where('maintenance_actions.performed_at', '>=', CarbonImmutable::instance($from)->utc())
            ->where('maintenance_actions.performed_at', '<', CarbonImmutable::instance($until)->utc())
            ->when($technicianId !== null, fn (Builder $actions) => $actions->where('maintenance_actions.technician_id', $technicianId));
    }

    protected function casts(): array
    {
        return [
            'performed_at' => 'immutable_datetime',
        ];
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
