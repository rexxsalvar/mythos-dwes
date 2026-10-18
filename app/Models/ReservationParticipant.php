<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationParticipant extends Model
{
    use HasFactory;

    protected $fillable = ['reservation_id', 'user_id', 'position', 'display_name', 'arrived_at'];

    protected function casts(): array
    {
        return ['arrived_at' => 'immutable_datetime'];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
