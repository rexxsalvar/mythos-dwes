<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MonthlyActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active && $this->user()->can('administration.view');
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'date_format:Y-m'],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
        ];
    }
}
