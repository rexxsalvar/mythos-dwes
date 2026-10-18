<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['location_id', 'archived_at', 'experience_id', 'name', 'location', 'capacity', 'status'];

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }

    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    protected function casts(): array
    {
        return ['archived_at' => 'immutable_datetime'];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function installations(): HasMany
    {
        return $this->hasMany(RoomInstallation::class);
    }

    public function installedExperiences(): BelongsToMany
    {
        return $this->belongsToMany(Experience::class, 'room_experience')
            ->withPivot(['id', 'installed_from', 'installed_until', 'notes'])->withTimestamps();
    }

    public function availabilityBlocks(): HasMany
    {
        return $this->hasMany(AvailabilityBlock::class);
    }
}
