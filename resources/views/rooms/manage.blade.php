<x-layout title="Gestionar salas">
    <div class="page-heading"><h1>Salas</h1><a href="{{ route('administration.index') }}">Volver a administración</a></div>
    @foreach ($errors->get('catalogue') as $error)<p class="field-error" role="alert">{{ $error }}</p>@endforeach
    <div class="panel">
        @foreach ($rooms as $record)<p><a href="{{ route('administration.rooms.edit', $record) }}">{{ $record->name }}</a> · {{ $record->experience->name }} · {{ $record->status }}</p>@endforeach
        <a href="{{ route('administration.rooms.index') }}">Crear sala</a>
    </div>
    <form method="POST" action="{{ $room->exists ? route('administration.rooms.update', $room) : route('administration.rooms.store') }}" class="panel narrow-panel" novalidate data-submit>
        @csrf
        @if ($room->exists) @method('PATCH') @endif
        <h2>{{ $room->exists ? 'Editar sala' : 'Nueva sala' }}</h2>
        <x-label for="name">Nombre</x-label><x-input name="name" :value="$room->name" required />
        <x-label for="experience_id">Experiencia</x-label>
        <x-select name="experience_id" required>
            @foreach ($experiences as $experience)<option value="{{ $experience->id }}" @selected(old('experience_id', $room->experience_id) == $experience->id)>{{ $experience->name }}</option>@endforeach
        </x-select>
        <x-label for="location">Ubicación</x-label><x-input name="location" :value="$room->location" required />
        <x-label for="capacity">Aforo</x-label><x-input name="capacity" type="number" :value="$room->capacity" min="1" required />
        <x-label for="status">Estado</x-label>
        <x-select name="status">
            @foreach (['operational' => 'Operativa', 'maintenance' => 'Mantenimiento', 'inactive' => 'Inactiva'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $room->status) == $value)>{{ $label }}</option>@endforeach
        </x-select>
        <button class="button" type="submit">Guardar sala</button>
    </form>
    @if ($room->exists && !$room->archived_at)<form method="POST" action="{{ route('administration.rooms.destroy', $room) }}">@csrf @method('DELETE')<button class="button" type="submit">Archivar sala</button></form>@endif
</x-layout>
