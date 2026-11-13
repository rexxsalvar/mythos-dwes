<x-layout :title="$experience->name">
    <div class="page-heading"><h1>{{ $experience->name }}</h1><p>{{ $experience->description }}</p><p>{{ $experience->duration_minutes }} minutos · {{ $experience->min_players }}–{{ $experience->max_players }} personas</p></div>
    <form method="GET" class="panel narrow-panel">
        <x-label for="players">Personas del grupo</x-label>
        <x-input name="players" type="number" :value="$players" :min="$experience->min_players" :max="$experience->max_players" required />
        <button class="button" type="submit">Consultar disponibilidad</button>
    </form>
    @if ($rate)
        <p>Importe total del grupo · {{ number_format($rate->total_cents / 100, 2, ',', '.') }} {{ $rate->currency }}</p>
    @else
        <p>No hay una tarifa vigente para este grupo.</p>
    @endif
    @foreach ($errors->get('session') as $error)<p class="field-error" role="alert">{{ $error }}</p>@endforeach
    @forelse ($sessions as $session)
        <section class="panel">
            <h2>{{ $session->starts_at->timezone('Europe/Madrid')->format('d/m/Y H:i') }} · {{ $session->room->name }}</h2>
            <p>{{ $session->room->location }}</p>
            @if ($rate)
                @can('create', App\Models\Reservation::class)
                    <form method="POST" action="{{ route(auth()->user()->can('reservations.view-all') ? 'personal.reservas.store' : 'cliente.reservas.store') }}" novalidate data-submit>
                        @csrf
                        <input type="hidden" name="game_session_id" value="{{ $session->id }}">
                        <input type="hidden" name="players" value="{{ $players }}">
                        @can('reservations.manage-all')
                            <x-label :for="'customer-'.$session->id">Identificador del cliente</x-label>
                            <x-input name="customer_id" :id="'customer-'.$session->id" type="number" min="1" />
                        @endcan
                        <button class="button" type="submit">Reservar para {{ $players }} personas</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Acceder para reservar</a>
                @endcan
            @endif
        </section>
    @empty
        <p>No hay sesiones disponibles para este grupo en los próximos tres meses.</p>
    @endforelse
    {{ $sessions->links() }}
    <a href="{{ route('experiences.index') }}">Volver a experiencias</a>
</x-layout>
