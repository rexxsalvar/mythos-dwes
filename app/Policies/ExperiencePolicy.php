<?php

namespace App\Policies;

use App\Models\Experience;
use App\Models\User;

class ExperiencePolicy
{
    public function viewAny(?User $user): bool
    {
        return $user === null || $user->is_active;
    }

    public function view(?User $user, Experience $experience): bool
    {
        return $this->manage($user) || (
            $this->viewAny($user) && $experience->is_active && $experience->archived_at === null
        );
    }

    public function manage(?User $user): bool
    {
        return $user !== null && $user->is_active && $user->can('experiences.manage');
    }

    public function create(User $user): bool
    {
        return $this->manage($user);
    }

    public function update(User $user, Experience $experience): bool
    {
        return $this->manage($user);
    }

    public function delete(User $user, Experience $experience): bool
    {
        return $this->manage($user);
    }
}
