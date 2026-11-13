<?php

namespace App\Http\Requests;

use App\Models\GameSession;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('session');

        return $session ? $this->user()->can('update', $session) : $this->user()->can('create', GameSession::class);
    }

    public function rules(): array
    {
        return [
            'room_experience_id' => ['required', 'integer', 'exists:room_experience,id'],
            'game_master_id' => ['required', 'integer', 'exists:users,id'],
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'ends_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:starts_at'],
            'locale' => ['required', 'in:es,en'],
        ];
    }
}
