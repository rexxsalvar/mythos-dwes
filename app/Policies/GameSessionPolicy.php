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

    public function create(User $user): bool
    {
        return $user->is_active && $user->can('sessions.manage');
    }

    public function update(User $user, GameSession $session): bool
    {
        return $session->status === 'scheduled' && $this->create($user);
    }

    public function delete(User $user, GameSession $session): bool
    {
        return $this->update($user, $session);
    }

    public function operate(User $user, GameSession $session): bool
    {
        return $user->is_active && in_array($session->status, ['scheduled', 'preparing', 'in_progress'], true) && (
            $user->can('sessions.manage') ||
            ($user->can('sessions.operate-assigned') && $session->game_master_id === $user->id)
        );
    }
}
