<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ['reference', 'user_id', 'game_session_id', 'team_id', 'players', 'total_cents', 'currency', 'status', 'expires_at', 'confirmed_at', 'cancelled_at', 'needs_review'];

    protected function casts(): array
    {
        return [
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
}
