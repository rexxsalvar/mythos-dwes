<?php

namespace App\Services;

use App\Models\Experience;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CatalogService
{
    public function saveExperience(User $actor, array $data, ?Experience $experience = null): Experience
    {
        return DB::transaction(function () use ($actor, $data, $experience) {
            $experience = $experience ? Experience::findOrFail($experience->id) : new Experience;
            Gate::forUser($actor)->authorize($experience->exists ? 'update' : 'create', $experience->exists ? $experience : Experience::class);
            if ($experience->exists && $experience->gameSessions()->where('ends_at', '>', now())
                ->whereHas('reservations', fn ($query) => $query->activeAt(now())
                    ->where(fn ($group) => $group->where('players', '<', $data['min_players'])->orWhere('players', '>', $data['max_players'])))
                ->exists()) {
                throw ValidationException::withMessages(['max_players' => 'El aforo debe admitir los grupos que ya tienen reserva.']);
            }
            $experience->fill($data)->save();

            return $experience;
        });
    }

    public function saveRoom(User $actor, array $data, ?Room $room = null): Room
    {
        return DB::transaction(function () use ($actor, $data, $room) {
            $room = $room ? Room::findOrFail($room->id) : new Room;
            Gate::forUser($actor)->authorize($room->exists ? 'update' : 'create', $room->exists ? $room : Room::class);
            if ($room->exists && $room->gameSessions()->where('ends_at', '>', now())
                ->whereHas('reservations', fn ($query) => $query->activeAt(now()))
                ->exists() && ($data['status'] !== $room->status || $data['capacity'] < $room->capacity)) {
                throw ValidationException::withMessages(['capacity' => 'Revisa las reservas futuras antes de reducir aforo o disponibilidad.']);
            }
            if ($room->exists && (int) $data['experience_id'] !== $room->experience_id) {
                throw ValidationException::withMessages(['experience_id' => 'El cambio de instalación debe conservar su historial. Crea una sala o gestiona una instalación nueva.']);
            }
            $new = ! $room->exists;
            $room->fill($data)->save();
            if ($new) {
                $room->installations()->create(['experience_id' => $room->experience_id, 'installed_from' => now()->utc()->startOfSecond()]);
            }

            return $room;
        });
    }

    public function archive(User $actor, Experience|Room $record): void
    {
        DB::transaction(function () use ($actor, $record) {
            $current = $record->newQuery()->findOrFail($record->id);
            Gate::forUser($actor)->authorize('delete', $current);
            if ($current->gameSessions()->where('ends_at', '>', now())
                ->whereHas('reservations', fn ($query) => $query->activeAt(now()))->exists()) {
                throw ValidationException::withMessages(['catalogue' => 'Revisa las reservas futuras antes de archivar este registro.']);
            }
            $current->update(['archived_at' => now()] + ($current instanceof Experience ? ['is_active' => false] : ['status' => 'inactive']));
        });
    }
}
