<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'duration_minutes', 'min_players', 'max_players', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(Rate::class);
    }

    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function hints(): HasMany
    {
        return $this->hasMany(Hint::class);
    }
}
