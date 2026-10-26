<?php

namespace App\Policies;

use App\Models\GameSession;
use App\Models\User;

class GameSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && ($user->can('sessions.view-all') || $user->can('sessions.view-assigned'));
    }

    public function view(User $user, GameSession $session): bool
    {
        return $user->is_active && (
            $user->can('sessions.view-all') ||
            ($user->can('sessions.view-assigned') && $session->game_master_id === $user->id)
        );
    }
}
