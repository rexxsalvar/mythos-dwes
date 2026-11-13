<?php

namespace App\Http\Controllers;

use App\Http\Requests\HoldReservationRequest;
use App\Models\GameSession;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function own(Request $request): View
    {
        Gate::authorize('viewAny', Reservation::class);
        $filters = $request->validate(['upcoming' => ['nullable', 'boolean']]);

        return view('reservations.index', [
            'reservations' => $request->user()->reservations()
                ->when($filters['upcoming'] ?? false, fn ($query) => $query->upcoming())
                ->with('gameSession.experience', 'gameSession.room')->latest('id')->paginate(10)->withQueryString(),
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

    public function store(HoldReservationRequest $request, ReservationService $service): RedirectResponse
    {
        $reservation = $service->hold(
            $request->user(),
            GameSession::findOrFail($request->integer('game_session_id')),
            $request->integer('players'),
            $request->filled('customer_id') ? User::findOrFail($request->integer('customer_id')) : null,
        );

        $route = $request->user()->can('reservations.view-all') ? 'personal.reservas.show' : 'cliente.reservas.show';

        return redirect()->route($route, $reservation)->with('status', 'Reserva pendiente. Consulta aquí el plazo de confirmación.');
    }

    public function confirm(Request $request, Reservation $reservation, ReservationService $service): RedirectResponse
    {
        $service->confirm($request->user(), $reservation);

        return redirect()->route('personal.reservas.show', $reservation)->with('status', 'Reserva confirmada.');
    }

    public function update(Request $request, Reservation $reservation, ReservationService $service): RedirectResponse
    {
        Gate::authorize('update', $reservation);
        $data = $request->validate(['players' => ['required', 'integer', 'min:1']]);
        $service->changeGroup($request->user(), $reservation, (int) $data['players']);

        return redirect()->route('personal.reservas.show', $reservation)->with('status', 'Grupo actualizado con su tarifa vigente.');
    }

    public function cancel(Request $request, Reservation $reservation, ReservationService $service): RedirectResponse
    {
        $service->cancel($request->user(), $reservation);

        $route = $request->user()->can('reservations.view-all') ? 'personal.reservas.show' : 'cliente.reservas.show';

        return redirect()->route($route, $reservation)->with('status', 'Reserva cancelada.');
    }
}
