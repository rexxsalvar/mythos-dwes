<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GameSession extends Model
{
    use HasFactory;

    protected $fillable = ['experience_id', 'room_id', 'game_master_id', 'starts_at', 'ends_at', 'status', 'preparation_minutes'];

    protected function casts(): array
    {
        return [
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
}
