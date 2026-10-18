<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomInstallation extends Model
{
    use HasFactory;

    protected $fillable = ['room_id', 'experience_id', 'installed_from', 'installed_until', 'notes'];

    protected $table = 'room_experience';

    protected function casts(): array
    {
        return ['installed_from' => 'immutable_datetime', 'installed_until' => 'immutable_datetime'];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }

    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class, 'room_experience_id');
    }
}
