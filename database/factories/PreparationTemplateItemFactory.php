<?php

namespace Database\Factories;

use App\Models\PreparationTemplate;
use App\Models\PreparationTemplateItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PreparationTemplateItem> */
class PreparationTemplateItemFactory extends Factory
{
    protected $model = PreparationTemplateItem::class;

    public function definition(): array
    {
        return ['preparation_template_id' => PreparationTemplate::factory(), 'position' => 1, 'label' => 'Comprobar mecanismo ficticio', 'is_required' => true];
    }
}
