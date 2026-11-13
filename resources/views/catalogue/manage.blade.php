<x-layout title="Gestionar experiencias">
    <div class="page-heading"><h1>Experiencias</h1><a href="{{ route('administration.index') }}">Volver a administración</a></div>
    @foreach ($errors->get('catalogue') as $error)<p class="field-error" role="alert">{{ $error }}</p>@endforeach
    <div class="panel">
        @foreach ($experiences as $record)
            <p><a href="{{ route('administration.experiences.edit', $record) }}">{{ $record->name }}</a> · {{ $record->archived_at ? 'Archivada' : 'Publicada' }}</p>
        @endforeach
        <a href="{{ route('administration.experiences.index') }}">Crear experiencia</a>
    </div>
    <form method="POST" action="{{ $experience->exists ? route('administration.experiences.update', $experience) : route('administration.experiences.store') }}" class="panel narrow-panel" novalidate data-submit>
        @csrf
        @if ($experience->exists) @method('PATCH') @endif
        <h2>{{ $experience->exists ? 'Editar experiencia' : 'Nueva experiencia' }}</h2>
        @foreach (['name' => 'Nombre', 'slug' => 'Identificador público', 'description' => 'Descripción'] as $field => $label)
            <x-label :for="$field">{{ $label }}</x-label>
            <x-input :name="$field" :value="$experience->$field" required />
        @endforeach
        @foreach (['duration_minutes' => 'Duración en minutos', 'min_players' => 'Mínimo de personas', 'max_players' => 'Máximo de personas'] as $field => $label)
            <x-label :for="$field">{{ $label }}</x-label>
            <x-input :name="$field" type="number" :value="$experience->$field" min="1" required />
        @endforeach
        <button class="button" type="submit">Guardar experiencia</button>
    </form>
    @if ($experience->exists && !$experience->archived_at)
        <form method="POST" action="{{ route('administration.experiences.destroy', $experience) }}">@csrf @method('DELETE')<button class="button" type="submit">Archivar experiencia</button></form>
    @endif
</x-layout>
