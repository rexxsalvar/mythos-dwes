<?php

namespace Database\Factories;

use App\Models\BookingPolicyVersion;
use App\Models\Experience;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BookingPolicyVersion> */
class BookingPolicyVersionFactory extends Factory
{
    protected $model = BookingPolicyVersion::class;

    public function definition(): array
    {
        return ['experience_id' => Experience::factory(), 'version' => 1, 'name' => 'Condiciones pendientes de análisis', 'status' => 'draft', 'cancellation_notice_minutes' => null, 'pending_expiry_minutes' => null, 'preparation_minutes' => null];
    }
}
