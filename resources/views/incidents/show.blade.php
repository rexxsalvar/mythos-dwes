<x-layout title="Detalle de incidencia">
    <div class="page-heading"><p class="eyebrow">MANTENIMIENTO</p><h1>{{ $incident->title }}</h1><p class="muted">{{ $incident->room->name }}</p></div>
    <section class="panel narrow-panel"><div class="role-list"><x-badge :status="$incident->severity" /><x-badge :status="$incident->status" /></div><p>{{ $incident->description }}</p><dl class="details"><div><dt>Técnico asignado</dt><dd>{{ $incident->assignee?->name ?? 'Sin asignar' }}</dd></div></dl><h2>Actuaciones</h2>@forelse($incident->maintenanceActions as $action)<div class="action-entry"><p>{{ $action->description }}</p><p class="muted">{{ $action->performed_at->timezone('Europe/Madrid')->format('d/m/Y H:i') }}</p></div>@empty<p class="muted">Todavía no hay actuaciones registradas.</p>@endforelse<a class="text-link" href="{{ route('mantenimiento.incidencias.index') }}">← Volver a incidencias</a></section>
    <form method="GET" class="panel narrow-panel">
        <h2>Filtrar actuaciones por fecha</h2>
        <x-label for="from">Desde</x-label><x-date name="from" :value="request('from')" />
        <x-label for="until">Hasta</x-label><x-date name="until" :value="request('until')" />
        <button class="button" type="submit">Aplicar filtro</button>
        <a href="{{ route('mantenimiento.incidencias.show', $incident) }}">Limpiar filtro</a>
    </form>
</x-layout>
