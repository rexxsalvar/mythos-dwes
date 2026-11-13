<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;

class IncidentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && ($user->can('incidents.view-all') || $user->can('incidents.view-assigned'));
    }

    public function view(User $user, Incident $incident): bool
    {
        return $user->is_active && (
            $user->can('incidents.view-all') ||
            ($user->can('incidents.view-assigned') && $incident->assigned_to === $user->id)
        );
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->can('incidents.report');
    }

    public function update(User $user, Incident $incident): bool
    {
        return $user->is_active && (
            $user->can('incidents.manage-all') ||
            ($user->can('incidents.manage-assigned') && $incident->assigned_to === $user->id)
        );
    }

    public function delete(User $user, Incident $incident): bool
    {
        // Closing an incident through update retains its maintenance history.
        return false;
    }
}
