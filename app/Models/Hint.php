<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Hint extends Model
{
    use HasFactory;

    protected $fillable = ['experience_id', 'code', 'description'];

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }

    public function gameSessions(): BelongsToMany
    {
        return $this->belongsToMany(GameSession::class)->using(HintUsage::class)
            ->withPivot(['used_at', 'penalty_seconds'])->withTimestamps();
    }
}
