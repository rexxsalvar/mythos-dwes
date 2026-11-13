<x-layout title="Detalle de partida">
    <div class="page-heading"><p class="eyebrow">DETALLE DE PARTIDA</p><h1>{{ $session->experience->name }}</h1><p class="muted">{{ $session->room->name }}</p></div>
    <section class="panel narrow-panel"><x-badge :status="$session->status" /><dl class="details"><div><dt>Inicio</dt><dd>{{ $session->starts_at->timezone('Europe/Madrid')->format('d/m/Y H:i') }}</dd></div><div><dt>Fin</dt><dd>{{ $session->ends_at->timezone('Europe/Madrid')->format('d/m/Y H:i') }}</dd></div><div><dt>Game master</dt><dd>{{ $session->gameMaster->name }}</dd></div><div><dt>Idioma de la partida</dt><dd>{{ $session->locale === 'en' ? 'Inglés' : 'Español' }}</dd></div></dl><a class="text-link" href="{{ route('personal.sesiones.index') }}">← Volver a la agenda</a></section>
    @foreach ($errors->all() as $error)<p class="field-error" role="alert">{{ $error }}</p>@endforeach
    @can('update', $session)
        <p><a href="{{ route('administration.sessions.edit', $session) }}">Editar sesión</a></p>
    @endcan
    @can('delete', $session)
        <form method="POST" action="{{ route('administration.sessions.destroy', $session) }}" data-submit>
            @csrf @method('DELETE')<button class="button" type="submit">Cancelar sesión</button>
        </form>
    @endcan
</x-layout>
