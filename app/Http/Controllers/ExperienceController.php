<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExperienceRequest;
use App\Models\Experience;
use App\Models\GameSession;
use App\Models\Rate;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExperienceController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Experience::class);

        return view('catalogue.index', ['experiences' => Experience::published()->orderBy('name')->paginate(10)]);
    }

    public function show(Request $request, Experience $experience)
    {
        Gate::authorize('view', $experience);
        $filters = $request->validate(['players' => ['nullable', 'integer', 'min:1']]);
        $players = (int) ($filters['players'] ?? $experience->min_players);
        $now = now()->toImmutable();

        return view('catalogue.show', [
            'experience' => $experience,
            'players' => $players,
            'rate' => Rate::effectiveForGroup($experience->id, $players, 'EUR', $now)->first(),
            'sessions' => GameSession::availableBetween($now, $now->addMonths(3), $experience->id, $players, $now)
                ->with('room')->orderBy('starts_at')->paginate(10)->withQueryString(),
        ]);
    }

    public function manage()
    {
        Gate::authorize('create', Experience::class);

        return view('catalogue.manage', ['experiences' => Experience::orderBy('name')->get(), 'experience' => new Experience]);
    }

    public function edit(Experience $experience)
    {
        Gate::authorize('update', $experience);

        return view('catalogue.manage', ['experiences' => Experience::orderBy('name')->get(), 'experience' => $experience]);
    }

    public function store(ExperienceRequest $request, CatalogService $service)
    {
        $service->saveExperience($request->user(), $request->validated());

        return redirect()->route('administration.experiences.index')->with('status', 'Experiencia creada.');
    }

    public function update(ExperienceRequest $request, Experience $experience, CatalogService $service)
    {
        $service->saveExperience($request->user(), $request->validated(), $experience);

        return redirect()->route('administration.experiences.index')->with('status', 'Experiencia guardada.');
    }

    public function destroy(Request $request, Experience $experience, CatalogService $service)
    {
        $service->archive($request->user(), $experience);

        return redirect()->route('administration.experiences.index')->with('status', 'Experiencia archivada.');
    }
}
