<?php

namespace Database\Factories;

use App\Models\Experience;
use App\Models\ExperienceLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ExperienceLanguage> */
class ExperienceLanguageFactory extends Factory
{
    protected $model = ExperienceLanguage::class;

    public function definition(): array
    {
        return ['experience_id' => Experience::factory(), 'locale' => 'es'];
    }
}
