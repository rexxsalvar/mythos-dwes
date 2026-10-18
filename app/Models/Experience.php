<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = ['archived_at', 'cover_image_path', 'name', 'slug', 'description', 'duration_minutes', 'min_players', 'max_players', 'is_active'];

    protected function casts(): array
    {
        return ['archived_at' => 'immutable_datetime',
            'is_active' => 'boolean',
        ];
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(Rate::class);
    }

    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function hints(): HasMany
    {
        return $this->hasMany(Hint::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ExperienceTranslation::class);
    }

    public function languages(): HasMany
    {
        return $this->hasMany(ExperienceLanguage::class);
    }

    public function installations(): HasMany
    {
        return $this->hasMany(RoomInstallation::class);
    }

    public function installedRooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_experience')
            ->withPivot(['id', 'installed_from', 'installed_until', 'notes'])->withTimestamps();
    }

    public function ratePlans(): HasMany
    {
        return $this->hasMany(RatePlan::class);
    }

    public function bookingPolicies(): HasMany
    {
        return $this->hasMany(BookingPolicyVersion::class);
    }

    public function preparationTemplates(): HasMany
    {
        return $this->hasMany(PreparationTemplate::class);
    }

    public function giftVouchers(): HasMany
    {
        return $this->hasMany(GiftVoucher::class);
    }
}
