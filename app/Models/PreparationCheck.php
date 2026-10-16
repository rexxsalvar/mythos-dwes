<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreparationCheck extends Model
{
    use HasFactory;

    protected $fillable = ['game_session_id', 'checked_by', 'label', 'is_required', 'passed', 'checked_at'];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'passed' => 'boolean',
            'checked_at' => 'immutable_datetime',
        ];
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
