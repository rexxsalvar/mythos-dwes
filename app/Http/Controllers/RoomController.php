<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomRequest;
use App\Models\Experience;
use App\Models\Room;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoomController extends Controller
{
    public function index()
    {
        Gate::authorize('create', Room::class);

        return $this->form(new Room);
    }

    public function edit(Room $room)
    {
        Gate::authorize('update', $room);

        return $this->form($room);
    }

    private function form(Room $room)
    {
        return view('rooms.manage', ['room' => $room, 'rooms' => Room::with('experience')->orderBy('name')->get(), 'experiences' => Experience::published()->orderBy('name')->get()]);
    }

    public function store(RoomRequest $request, CatalogService $service)
    {
        $service->saveRoom($request->user(), $request->validated());

        return redirect()->route('administration.rooms.index')->with('status', 'Sala creada.');
    }

    public function update(RoomRequest $request, Room $room, CatalogService $service)
    {
        $service->saveRoom($request->user(), $request->validated(), $room);

        return redirect()->route('administration.rooms.index')->with('status', 'Sala guardada.');
    }

    public function destroy(Request $request, Room $room, CatalogService $service)
    {
        $service->archive($request->user(), $room);

        return redirect()->route('administration.rooms.index')->with('status', 'Sala archivada.');
    }
}
