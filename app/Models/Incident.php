<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = ['room_id', 'game_session_id', 'reported_by', 'assigned_to', 'title', 'description', 'severity', 'status', 'resolved_at'];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'immutable_datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function maintenanceActions(): HasMany
    {
        return $this->hasMany(MaintenanceAction::class);
    }
}
