<x-layout :title="$personal ? 'Reservas' : 'Mis reservas'">
    <div class="page-heading"><p class="eyebrow">{{ $personal ? 'ATENCIÓN A LOS GRUPOS' : 'TUS EXPERIENCIAS' }}</p><h1>{{ $personal ? 'Reservas' : 'Mis reservas' }}</h1><p class="muted">Consulta la sesión, los participantes y el estado de cada reserva.</p></div>
    <div class="panel table-wrap"><table><caption class="sr-only">{{ $personal ? 'Reservas de clientes' : 'Tus reservas' }}</caption><thead><tr><th>Referencia</th><th>Experiencia</th><th>Fecha</th><th>Grupo</th><th>Estado</th><th>Detalle</th></tr></thead><tbody>
        @forelse($reservations as $reservation)<tr><td>{{ $reservation->reference }}</td><td>{{ $reservation->gameSession->experience->name }}</td><td>{{ $reservation->gameSession->starts_at->timezone('Europe/Madrid')->format('d/m/Y H:i') }}</td><td>{{ $reservation->players }} personas</td><td><x-badge :status="$reservation->status" /></td><td><a href="{{ route($personal ? 'personal.reservas.show' : 'cliente.reservas.show', $reservation) }}" aria-label="Ver reserva {{ $reservation->reference }}">Ver reserva ↗</a></td></tr>
        @empty<tr><td colspan="6" class="empty-state">No hay reservas para mostrar.</td></tr>@endforelse
    </tbody></table></div>
    <div class="pagination">{{ $reservations->links() }}</div>
</x-layout>
