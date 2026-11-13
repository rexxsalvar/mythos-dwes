<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;

class HoldReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Reservation::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'game_session_id' => ['required', 'integer', 'exists:game_sessions,id'],
            'players' => ['required', 'integer', 'min:1'],
            'customer_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
