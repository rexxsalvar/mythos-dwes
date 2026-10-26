<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgendaFilterRequest;
use App\Models\GameSession;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GameSessionController extends Controller
{
    public function index(AgendaFilterRequest $request): View
    {
        Gate::authorize('viewAny', GameSession::class);
        $filters = $request->validated();
        $sessions = GameSession::with('experience', 'room', 'gameMaster');

        if (! $request->user()->can('sessions.view-all')) {
            $sessions->where('game_master_id', $request->user()->id);
        }

        // Agenda dates use Europe/Madrid; timestamps are stored in UTC.
        if (! empty($filters['from'])) {
            $sessions->where('starts_at', '>=', CarbonImmutable::parse($filters['from'], 'Europe/Madrid')->utc());
        }
        if (! empty($filters['until'])) {
            $sessions->where('starts_at', '<', CarbonImmutable::parse($filters['until'], 'Europe/Madrid')->addDay()->utc());
        }
        if (! empty($filters['room_id'])) {
            $sessions->where('room_id', $filters['room_id']);
        }

        return view('sessions.index', [
            'sessions' => $sessions->orderBy('starts_at')->paginate(10)->withQueryString(),
            'rooms' => Room::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(GameSession $session): View
    {
        Gate::authorize('view', $session);

        return view('sessions.show', ['session' => $session->load('experience', 'room', 'gameMaster')]);
    }
}
