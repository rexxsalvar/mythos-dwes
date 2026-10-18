<?php

namespace Database\Factories;

use App\Models\PaymentTransaction;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PaymentTransaction> */
class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    public function definition(): array
    {
        return ['reference' => fake()->unique()->uuid(), 'reservation_id' => Reservation::factory(), 'gift_voucher_id' => null, 'parent_id' => null, 'recorded_by' => User::factory(), 'kind' => 'receipt', 'amount_cents' => 8000, 'currency' => 'EUR', 'method' => 'cash', 'status' => 'settled', 'paid_at' => '2026-10-10 12:00:00'];
    }
}
