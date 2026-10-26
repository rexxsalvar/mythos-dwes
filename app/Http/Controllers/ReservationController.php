<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function own(Request $request): View
    {
        Gate::authorize('viewAny', Reservation::class);

        return view('reservations.index', [
            'reservations' => $request->user()->reservations()->with('gameSession.experience', 'gameSession.room')->latest('id')->paginate(10),
            'personal' => false,
        ]);
    }

    public function index(): View
    {
        Gate::authorize('viewAny', Reservation::class);

        return view('reservations.index', [
            'reservations' => Reservation::with('gameSession.experience', 'gameSession.room', 'user')->latest('id')->paginate(10),
            'personal' => true,
        ]);
    }

    public function show(Reservation $reservation): View
    {
        Gate::authorize('view', $reservation);

        return view('reservations.show', ['reservation' => $reservation->load('gameSession.experience', 'gameSession.room')]);
    }
}
