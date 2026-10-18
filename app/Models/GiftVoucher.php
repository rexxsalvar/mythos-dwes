<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GiftVoucher extends Model
{
    use HasFactory;

    protected $fillable = ['code_hash', 'experience_id', 'purchased_by', 'issued_by', 'kind', 'amount_cents', 'players', 'currency', 'issued_at', 'expires_at', 'voided_at', 'recipient_name', 'message'];

    protected function casts(): array
    {
        return ['issued_at' => 'immutable_datetime', 'expires_at' => 'immutable_datetime', 'voided_at' => 'immutable_datetime'];
    }

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }

    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchased_by');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function redemption(): HasOne
    {
        return $this->hasOne(GiftVoucherRedemption::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
