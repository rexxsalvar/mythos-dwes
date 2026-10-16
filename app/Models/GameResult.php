<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameResult extends Model
{
    use HasFactory;

    protected $fillable = ['game_session_id', 'recorded_by', 'escaped', 'elapsed_seconds', 'notes'];

    protected function casts(): array
    {
        return [
            'escaped' => 'boolean',
        ];
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
