<?php

namespace Database\Factories;

use App\Models\Experience;
use App\Models\ExperienceTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ExperienceTranslation> */
class ExperienceTranslationFactory extends Factory
{
    protected $model = ExperienceTranslation::class;

    public function definition(): array
    {
        return ['experience_id' => Experience::factory(), 'locale' => 'en', 'title' => 'Test adventure', 'description' => 'Fictional experience for test data.'];
    }
}
