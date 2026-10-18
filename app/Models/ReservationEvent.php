<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationEvent extends Model
{
    use HasFactory;

    protected $fillable = ['reservation_id', 'actor_id', 'action', 'occurred_at', 'reason', 'before_values', 'after_values'];

    protected function casts(): array
    {
        return ['occurred_at' => 'immutable_datetime', 'before_values' => 'array', 'after_values' => 'array'];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
