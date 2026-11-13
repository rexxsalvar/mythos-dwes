<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $room = $this->route('room');

        return $room ? $this->user()->can('update', $room) : $this->user()->can('create', Room::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('rooms')->ignore($this->route('room'))],
            'experience_id' => ['required', 'integer', 'exists:experiences,id'],
            'location' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(['operational', 'maintenance', 'inactive'])],
        ];
    }
}
