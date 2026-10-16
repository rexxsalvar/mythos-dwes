<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['experience_id', 'name', 'location', 'capacity', 'status'];

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
}
