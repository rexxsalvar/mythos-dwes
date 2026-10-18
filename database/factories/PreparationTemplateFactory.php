<?php

namespace Database\Factories;

use App\Models\Experience;
use App\Models\PreparationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PreparationTemplate> */
class PreparationTemplateFactory extends Factory
{
    protected $model = PreparationTemplate::class;

    public function definition(): array
    {
        return ['experience_id' => Experience::factory(), 'name' => 'Preparación demo', 'version' => 1, 'is_active' => true];
    }
}
