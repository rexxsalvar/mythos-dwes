<?php

namespace Database\Factories;

use App\Models\GiftVoucher;
use App\Models\GiftVoucherRedemption;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GiftVoucherRedemption> */
class GiftVoucherRedemptionFactory extends Factory
{
    protected $model = GiftVoucherRedemption::class;

    public function definition(): array
    {
        return ['gift_voucher_id' => GiftVoucher::factory(), 'reservation_id' => Reservation::factory(), 'redeemed_by' => fn (array $a) => Reservation::findOrFail($a['reservation_id'])->user_id, 'redeemed_at' => '2026-10-10 10:00:00', 'applied_cents' => 8000];
    }
}
