<?php

namespace App\Http\Controllers;

use App\Models\GameSession;
use App\Models\Incident;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard', ['user' => $request->user()]);
    }

    public function administration(): View
    {
        return view('administration.index', [
            'counts' => [
                'Usuarios' => User::count(), 'Reservas' => Reservation::count(),
                'Sesiones' => GameSession::count(), 'Incidencias' => Incident::count(),
            ],
            'roles' => Role::withCount('users')->orderBy('name')->get(),
        ]);
    }
}
