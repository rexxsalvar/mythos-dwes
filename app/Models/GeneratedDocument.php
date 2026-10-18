<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedDocument extends Model
{
    use HasFactory;

    protected $fillable = ['reference', 'owner_id', 'requested_by', 'reservation_id', 'game_result_id', 'kind', 'format', 'locale', 'parameters', 'status', 'disk', 'path', 'generated_at', 'checksum', 'last_error'];

    protected function casts(): array
    {
        return ['parameters' => 'array', 'generated_at' => 'immutable_datetime'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function gameResult(): BelongsTo
    {
        return $this->belongsTo(GameResult::class);
    }
}
