<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreparationTemplateItem extends Model
{
    use HasFactory;

    protected $fillable = ['preparation_template_id', 'position', 'label', 'is_required'];

    protected function casts(): array
    {
        return ['is_required' => 'boolean'];
    }

    public function preparationTemplate(): BelongsTo
    {
        return $this->belongsTo(PreparationTemplate::class);
    }

    public function checks(): HasMany
    {
        return $this->hasMany(PreparationCheck::class);
    }
}
