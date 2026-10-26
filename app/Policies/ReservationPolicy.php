<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && ($user->can('reservations.view-own') || $user->can('reservations.view-all'));
    }

    public function view(User $user, Reservation $reservation): bool
    {
        return $user->is_active && (
            $user->can('reservations.view-all') ||
            ($user->can('reservations.view-own') && $reservation->user_id === $user->id)
        );
    }
}
