<?php

namespace App\Http\Requests;

use App\Models\Experience;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $experience = $this->route('experience');

        return $experience ? $this->user()->can('update', $experience) : $this->user()->can('create', Experience::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('experiences')->ignore($this->route('experience'))],
            'description' => ['required', 'string', 'max:10000'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'min_players' => ['required', 'integer', 'min:1'],
            'max_players' => ['required', 'integer', 'gte:min_players'],
        ];
    }
}
