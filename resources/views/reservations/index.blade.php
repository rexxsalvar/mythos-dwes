<x-layout :title="$personal ? 'Reservas' : 'Mis reservas'">
    <div class="page-heading"><p class="eyebrow">{{ $personal ? 'ATENCIÓN A LOS GRUPOS' : 'TUS EXPERIENCIAS' }}</p><h1>{{ $personal ? 'Reservas' : 'Mis reservas' }}</h1><p class="muted">Consulta la sesión, los participantes y el estado de cada reserva.</p></div>
    <div class="panel table-wrap" role="region" aria-label="{{ $personal ? 'Reservas de clientes' : 'Tus reservas' }}" tabindex="0"><table><caption class="sr-only">{{ $personal ? 'Reservas de clientes' : 'Tus reservas' }}</caption><thead><tr><th scope="col">Referencia</th><th scope="col">Experiencia</th><th scope="col">Fecha</th><th scope="col">Grupo</th><th scope="col">Estado</th><th scope="col">Detalle</th></tr></thead><tbody>
        @forelse($reservations as $reservation)<tr><td>{{ $reservation->reference }}</td><td>{{ $reservation->gameSession->experience->name }}</td><td>{{ $reservation->gameSession->starts_at->timezone('Europe/Madrid')->format('d/m/Y H:i') }}</td><td>{{ $reservation->players }} personas</td><td><x-badge :status="$reservation->status" /></td><td><a href="{{ route($personal ? 'personal.reservas.show' : 'cliente.reservas.show', $reservation) }}" aria-label="Ver reserva {{ $reservation->reference }}">Ver reserva ↗</a></td></tr>
        @empty<tr><td colspan="6" class="empty-state"><p>No hay reservas para mostrar.</p></td></tr>@endforelse
    </tbody></table></div>
    <div class="pagination">{{ $reservations->links() }}</div>
    @unless($personal)
        <form method="GET" action="{{ route('cliente.reservas.index') }}">
            <x-checkbox name="upcoming" :checked="request()->boolean('upcoming')">Solo próximas partidas</x-checkbox>
            <button class="button" type="submit">Aplicar filtro</button>
        </form>
    @endunless
</x-layout>
