<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = ['experience_id', 'players', 'total_cents', 'currency'];

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }
}
