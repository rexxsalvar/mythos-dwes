<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgendaFilterRequest;
use App\Http\Requests\ScheduleSessionRequest;
use App\Models\GameSession;
use App\Models\Room;
use App\Models\RoomInstallation;
use App\Models\User;
use App\Services\ScheduleSession;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GameSessionController extends Controller
{
    public function create()
    {
        Gate::authorize('create', GameSession::class);

        return $this->form(new GameSession);
    }

    public function edit(GameSession $session)
    {
        Gate::authorize('update', $session);

        return $this->form($session);
    }

    private function form(GameSession $session)
    {
        return view('sessions.form', [
            'session' => $session,
            'installations' => RoomInstallation::with('room', 'experience')->get(),
            'masters' => User::role('game_master')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(ScheduleSessionRequest $request, ScheduleSession $service)
    {
        $data = $request->validated();
        $session = $service->create(
            $request->user(), RoomInstallation::findOrFail($data['room_experience_id']),
            User::findOrFail($data['game_master_id']),
            CarbonImmutable::createFromFormat('Y-m-d\TH:i', $data['starts_at'], 'Europe/Madrid')->startOfMinute(),
            CarbonImmutable::createFromFormat('Y-m-d\TH:i', $data['ends_at'], 'Europe/Madrid')->startOfMinute(),
            $data['locale'],
        );

        return redirect()->route('personal.sesiones.show', $session)->with('status', 'Sesión creada.');
    }

    public function update(ScheduleSessionRequest $request, GameSession $session, ScheduleSession $service)
    {
        $data = $request->validated();
        $service->update(
            $request->user(), $session, RoomInstallation::findOrFail($data['room_experience_id']),
            User::findOrFail($data['game_master_id']),
            CarbonImmutable::createFromFormat('Y-m-d\TH:i', $data['starts_at'], 'Europe/Madrid')->startOfMinute(),
            CarbonImmutable::createFromFormat('Y-m-d\TH:i', $data['ends_at'], 'Europe/Madrid')->startOfMinute(),
            $data['locale'],
        );

        return redirect()->route('personal.sesiones.show', $session)->with('status', 'Sesión guardada.');
    }

    public function destroy(Request $request, GameSession $session, ScheduleSession $service)
    {
        $service->cancel($request->user(), $session);

        return redirect()->route('personal.sesiones.show', $session)->with('status', 'Sesión cancelada.');
    }

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
