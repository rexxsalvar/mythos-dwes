<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDelivery extends Model
{
    use HasFactory;

    protected $fillable = ['idempotency_key', 'user_id', 'reservation_id', 'incident_id', 'purpose', 'channel', 'recipient', 'locale', 'status', 'scheduled_for', 'sent_at', 'attempts', 'last_error'];

    protected function casts(): array
    {
        return ['scheduled_for' => 'immutable_datetime', 'sent_at' => 'immutable_datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }
}
