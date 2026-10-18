<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftVoucherRedemption extends Model
{
    use HasFactory;

    protected $fillable = ['gift_voucher_id', 'reservation_id', 'redeemed_by', 'redeemed_at', 'applied_cents'];

    protected function casts(): array
    {
        return ['redeemed_at' => 'immutable_datetime'];
    }

    public function giftVoucher(): BelongsTo
    {
        return $this->belongsTo(GiftVoucher::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function redeemer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }
}
