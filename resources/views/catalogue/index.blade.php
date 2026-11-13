<x-layout title="Experiencias">
    <div class="page-heading"><h1>Experiencias</h1><p>Elige una experiencia para consultar tarifas y sesiones disponibles.</p></div>
    @forelse ($experiences as $experience)
        <article class="panel">
            <h2><a href="{{ route('experiences.show', $experience->slug) }}">{{ $experience->name }}</a></h2>
            <p>{{ $experience->description }}</p>
            <p>{{ $experience->duration_minutes }} minutos · {{ $experience->min_players }}–{{ $experience->max_players }} personas</p>
        </article>
    @empty
        <p>No hay experiencias publicadas.</p>
    @endforelse
    {{ $experiences->links() }}
</x-layout>
