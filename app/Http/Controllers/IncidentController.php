<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        Gate::authorize('viewAny', Incident::class);

        return view('incidents.index', [
            'incidents' => $this->visibleTo($request)->with('room')->latest('id')->paginate(10),
        ]);
    }

    public function show(Request $request, string $incident): View
    {
        $record = $this->visibleTo($request)->with('room', 'assignee', 'maintenanceActions')->findOrFail($incident);
        Gate::authorize('view', $record);
        $filters = $request->validate([
            'from' => ['nullable', 'required_with:until', 'date_format:Y-m-d'],
            'until' => ['nullable', 'required_with:from', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        if (! empty($filters['from']) && ! empty($filters['until'])) {
            $from = CarbonImmutable::parse($filters['from'], 'Europe/Madrid')->utc();
            $until = CarbonImmutable::parse($filters['until'], 'Europe/Madrid')->addDay()->utc();
            $record->setRelation('maintenanceActions', $record->maintenanceActions()->performedBetween($from, $until)->get());
        }

        return view('incidents.show', ['incident' => $record]);
    }
}
