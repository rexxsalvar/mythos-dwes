<?php

namespace Database\Factories;

use App\Models\GeneratedDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GeneratedDocument> */
class GeneratedDocumentFactory extends Factory
{
    protected $model = GeneratedDocument::class;

    public function definition(): array
    {
        return ['reference' => fake()->unique()->uuid(), 'owner_id' => User::factory(), 'requested_by' => User::factory(), 'reservation_id' => null, 'game_result_id' => null, 'kind' => 'monthly_report', 'format' => 'pdf', 'locale' => 'es', 'parameters' => ['month' => '2026-10'], 'status' => 'queued', 'disk' => 'local'];
    }
}
