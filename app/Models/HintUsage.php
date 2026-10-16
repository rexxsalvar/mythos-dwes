<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class HintUsage extends Pivot
{
    protected $table = 'game_session_hint';

    protected function casts(): array
    {
        return ['used_at' => 'immutable_datetime', 'penalty_seconds' => 'integer'];
    }
}
