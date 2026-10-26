<x-layout title="Incidencias">
    <div class="page-heading"><p class="eyebrow">MANTENIMIENTO</p><h1>Incidencias</h1><p class="muted">{{ auth()->user()->can('incidents.view-all') ? 'Consulta las incidencias registradas.' : 'Consulta las incidencias que tienes asignadas.' }}</p></div>
    <div class="panel table-wrap"><table><caption class="sr-only">Incidencias accesibles</caption><thead><tr><th>Incidencia</th><th>Sala</th><th>Gravedad</th><th>Estado</th><th>Detalle</th></tr></thead><tbody>
        @forelse($incidents as $incident)<tr><td>{{ $incident->title }}</td><td>{{ $incident->room->name }}</td><td><x-badge :status="$incident->severity" /></td><td><x-badge :status="$incident->status" /></td><td><a href="{{ route('mantenimiento.incidencias.show', $incident) }}">Ver incidencia ↗</a></td></tr>
        @empty<tr><td colspan="5" class="empty-state">No hay incidencias para mostrar.</td></tr>@endforelse
    </tbody></table></div><div class="pagination">{{ $incidents->links() }}</div>
</x-layout>
