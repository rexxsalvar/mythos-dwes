<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class RoomPolicy
{
    public function viewAny(?User $user): bool
    {
        return $user === null || $user->is_active;
    }

    public function view(?User $user, Room $room): bool
    {
        return $this->manage($user) || (
            $this->viewAny($user) && $room->archived_at === null && $room->status === 'operational'
            && $this->hasPublishedInstallation($room)
        );
    }

    public function manage(?User $user): bool
    {
        return $user !== null && $user->is_active && $user->can('rooms.manage');
    }

    public function create(User $user): bool
    {
        return $this->manage($user);
    }

    public function update(User $user, Room $room): bool
    {
        return $this->manage($user);
    }

    public function delete(User $user, Room $room): bool
    {
        return $this->manage($user);
    }

    public function operate(User $user, Room $room): bool
    {
        return $user->is_active && $user->can('rooms.operate') && $room->archived_at === null;
    }

    private function hasPublishedInstallation(Room $room): bool
    {
        $instant = now('UTC');

        return $room->installations()
            ->where('installed_from', '<=', $instant)
            ->where(fn (Builder $query) => $query->whereNull('installed_until')->orWhere('installed_until', '>', $instant))
            ->whereHas('experience', fn (Builder $query) => $query->published())
            ->exists();
    }
}
