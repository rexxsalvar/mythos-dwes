<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['reference', 'reservation_id', 'gift_voucher_id', 'parent_id', 'recorded_by', 'kind', 'amount_cents', 'currency', 'method', 'status', 'paid_at', 'notes'];

    protected function casts(): array
    {
        return ['paid_at' => 'immutable_datetime'];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function giftVoucher(): BelongsTo
    {
        return $this->belongsTo(GiftVoucher::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'parent_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'parent_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
