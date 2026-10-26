<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidentController extends Controller
{
    private function visibleTo(Request $request): Builder
    {
        return Incident::query()->when(
            ! $request->user()->can('incidents.view-all'),
            fn (Builder $query) => $query->where('assigned_to', $request->user()->id),
        );
    }

    public function index(Request $request): View
    {
        return view('incidents.index', [
            'incidents' => $this->visibleTo($request)->with('room')->latest('id')->paginate(10),
        ]);
    }

    public function show(Request $request, string $incident): View
    {
        return view('incidents.show', [
            'incident' => $this->visibleTo($request)->with('room', 'assignee', 'maintenanceActions')->findOrFail($incident),
        ]);
    }
}
