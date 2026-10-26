<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgendaFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sessions.view-all') || $this->user()?->can('sessions.view-assigned');
    }

    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'until' => ['nullable', 'date_format:Y-m-d', Rule::when($this->filled('from'), 'after_or_equal:from')],
            'room_id' => ['nullable', 'integer', Rule::exists('rooms', 'id')],
        ];
    }
}
