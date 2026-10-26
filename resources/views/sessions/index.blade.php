<x-layout title="Agenda de partidas">
    <div class="page-heading"><p class="eyebrow">ORGANIZACIÓN DE PARTIDAS</p><h1>Agenda</h1><p class="muted">{{ auth()->user()->can('sessions.view-all') ? 'Consulta las sesiones programadas y su responsable.' : 'Consulta las partidas que tienes asignadas.' }} Fechas y horas de Murcia.</p></div>
    <form method="GET" action="{{ route('personal.sesiones.index') }}" class="panel filter-form">
        <div><x-label for="from">Desde</x-label><x-date name="from" :value="request('from', '')" /></div>
        <div><x-label for="until">Hasta</x-label><x-date name="until" :value="request('until', '')" /></div>
        <div><x-label for="room_id">Sala</x-label><x-select name="room_id"><option value="">Todas las salas</option>@foreach($rooms as $room)<option value="{{ $room->id }}" @selected((string) old('room_id', request('room_id')) === (string) $room->id)>{{ $room->name }}</option>@endforeach</x-select></div>
        <div class="actions"><button class="button" type="submit">Filtrar</button><a class="text-link" href="{{ route('personal.sesiones.index') }}">Limpiar</a></div>
    </form>
    <div class="panel table-wrap"><table><caption class="sr-only">Sesiones de la agenda</caption><thead><tr><th>Fecha</th><th>Experiencia</th><th>Sala</th><th>Responsable</th><th>Estado</th><th>Detalle</th></tr></thead><tbody>
        @forelse($sessions as $session)<tr><td>{{ $session->starts_at->timezone('Europe/Madrid')->format('d/m/Y H:i') }}</td><td>{{ $session->experience->name }}</td><td>{{ $session->room->name }}</td><td>{{ $session->gameMaster->name }}</td><td><x-badge :status="$session->status" /></td><td><a href="{{ route('personal.sesiones.show', $session) }}" aria-label="Ver partida del {{ $session->starts_at->timezone('Europe/Madrid')->format('d/m/Y H:i') }}">Ver partida ↗</a></td></tr>
        @empty<tr><td colspan="6" class="empty-state">No hay partidas para los filtros seleccionados.</td></tr>@endforelse
    </tbody></table></div><div class="pagination">{{ $sessions->links() }}</div>
</x-layout>
