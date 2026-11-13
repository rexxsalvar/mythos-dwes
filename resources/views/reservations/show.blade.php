<x-layout :title="'Reserva '.$reservation->reference">
    <div class="page-heading"><p class="eyebrow">DETALLE DE RESERVA</p><h1>{{ $reservation->gameSession->experience->name }}</h1><p class="muted">{{ $reservation->reference }}</p></div>
    <section class="panel narrow-panel"><x-badge :status="$reservation->status" /><dl class="details"><div><dt>Fecha y hora</dt><dd>{{ $reservation->gameSession->starts_at->timezone('Europe/Madrid')->format('d/m/Y H:i') }} (hora de Murcia)</dd></div><div><dt>Sala</dt><dd>{{ $reservation->gameSession->room->name }}</dd></div><div><dt>Participantes</dt><dd>{{ $reservation->players }} personas</dd></div><div><dt>Importe del grupo</dt><dd>{{ number_format($reservation->total_cents / 100, 2, ',', '.') }} {{ $reservation->currency }}</dd></div></dl>
        <a class="text-link" href="{{ auth()->user()->can('reservations.view-all') ? route('personal.reservas.index') : route('cliente.reservas.index') }}">← Volver a reservas</a>
    </section>
    @if ($reservation->status === 'pending' && $reservation->expires_at)
        <p>Bloqueo pendiente hasta {{ $reservation->expires_at->timezone('Europe/Madrid')->format('d/m/Y H:i:s') }}.</p>
    @endif
    @if ($reservation->bookingPolicyVersion?->cancellation_notice_minutes !== null)
        <p>Plazo de cancelación de la reserva confirmada hasta {{ $reservation->gameSession->starts_at->subMinutes($reservation->bookingPolicyVersion->cancellation_notice_minutes)->timezone('Europe/Madrid')->format('d/m/Y H:i') }}.</p>
    @endif
    @foreach ($errors->all() as $error)<p class="field-error" role="alert">{{ $error }}</p>@endforeach
    @if ($reservation->needs_review)
        <p class="notice" role="status">La sesión está afectada por una incidencia o un bloqueo y necesita revisión por el personal.</p>
    @endif
    @can('confirm', $reservation)
        <form method="POST" action="{{ route('personal.reservas.confirm', $reservation) }}" data-submit>
            @csrf @method('PATCH')<button class="button" type="submit">Confirmar reserva</button>
        </form>
    @endcan
    @can('update', $reservation)
        <form method="POST" action="{{ route('personal.reservas.update', $reservation) }}" class="panel narrow-panel" novalidate data-submit>
            @csrf @method('PATCH')
            <x-label for="players">Personas del grupo</x-label>
            <x-input name="players" type="number" :value="$reservation->players" min="1" required />
            <button class="button" type="submit">Actualizar grupo y tarifa</button>
        </form>
    @endcan
    @can('cancel', $reservation)
        <form method="POST" action="{{ route(auth()->user()->can('reservations.view-all') ? 'personal.reservas.cancel' : 'cliente.reservas.cancel', $reservation) }}" data-submit>
            @csrf @method('DELETE')<button class="button" type="submit">Cancelar reserva</button>
        </form>
    @endcan
</x-layout>
