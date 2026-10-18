<?php

namespace Database\Factories;

use App\Models\GiftVoucher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GiftVoucher> */
class GiftVoucherFactory extends Factory
{
    protected $model = GiftVoucher::class;

    public function definition(): array
    {
        return ['code_hash' => hash('sha256', fake()->unique()->uuid()), 'experience_id' => null, 'purchased_by' => User::factory(), 'issued_by' => null, 'kind' => 'amount', 'amount_cents' => 10000, 'players' => null, 'currency' => 'EUR', 'issued_at' => '2026-01-01 10:00:00', 'expires_at' => null, 'recipient_name' => 'Destinatario ficticio'];
    }
}
