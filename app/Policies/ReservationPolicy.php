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

    public function create(User $user): bool
    {
        return $user->is_active && ($user->can('reservations.manage-own') || $user->can('reservations.manage-all'));
    }

    public function createFor(User $user, User $customer): bool
    {
        return $this->create($user) && $customer->is_active && (
            $user->can('reservations.manage-all') ||
            ($user->can('reservations.manage-own') && $user->is($customer))
        );
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $user->is_active && $user->can('reservations.manage-all') && $reservation->status === 'pending';
    }

    public function confirm(User $user, Reservation $reservation): bool
    {
        return $this->update($user, $reservation);
    }

    public function cancel(User $user, Reservation $reservation): bool
    {
        return $user->is_active && in_array($reservation->status, ['pending', 'confirmed'], true) && (
            $user->can('reservations.manage-all') ||
            ($user->can('reservations.manage-own') && $reservation->user_id === $user->id)
        );
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $this->cancel($user, $reservation);
    }
}
