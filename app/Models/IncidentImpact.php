<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class IncidentImpact extends Pivot
{
    use HasFactory;

    protected $fillable = ['reservation_id', 'incident_id', 'detected_at', 'reviewed_by', 'reviewed_at', 'resolution_note', 'status'];

    protected $table = 'reservation_incident';

    public $incrementing = true;

    protected function casts(): array
    {
        return ['detected_at' => 'immutable_datetime', 'reviewed_at' => 'immutable_datetime'];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
