<x-layout :title="'Reserva '.$reservation->reference">
    <div class="page-heading"><p class="eyebrow">DETALLE DE RESERVA</p><h1>{{ $reservation->gameSession->experience->name }}</h1><p class="muted">{{ $reservation->reference }}</p></div>
    <section class="panel narrow-panel"><x-badge :status="$reservation->status" /><dl class="details"><div><dt>Fecha y hora</dt><dd>{{ $reservation->gameSession->starts_at->timezone('Europe/Madrid')->format('d/m/Y H:i') }} (hora de Murcia)</dd></div><div><dt>Sala</dt><dd>{{ $reservation->gameSession->room->name }}</dd></div><div><dt>Participantes</dt><dd>{{ $reservation->players }} personas</dd></div><div><dt>Importe del grupo</dt><dd>{{ number_format($reservation->total_cents / 100, 2, ',', '.') }} {{ $reservation->currency }}</dd></div></dl>
        <a class="text-link" href="{{ auth()->user()->can('reservations.view-all') ? route('personal.reservas.index') : route('cliente.reservas.index') }}">← Volver a reservas</a>
    </section>
</x-layout>
