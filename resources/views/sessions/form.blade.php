<x-layout title="Programar sesión">
    <div class="page-heading"><h1>{{ $session->exists ? 'Editar sesión' : 'Programar sesión' }}</h1><p>Las horas son de Murcia. La preparación ocupa los 30 minutos anteriores al inicio.</p></div>
    @foreach ($errors->get('session') as $error)<p class="field-error" role="alert">{{ $error }}</p>@endforeach
    <form method="POST" action="{{ $session->exists ? route('administration.sessions.update', $session) : route('administration.sessions.store') }}" class="panel narrow-panel" novalidate data-submit>
        @csrf
        @if ($session->exists) @method('PATCH') @endif
        <x-label for="room_experience_id">Instalación de sala y experiencia</x-label>
        <x-select name="room_experience_id" required>
            @foreach ($installations as $installation)<option value="{{ $installation->id }}" @selected(old('room_experience_id', $session->room_experience_id) == $installation->id)>{{ $installation->room->name }} · {{ $installation->experience->name }} · {{ $installation->installed_from->format('d/m/Y') }}</option>@endforeach
        </x-select>
        <x-label for="game_master_id">Game master</x-label>
        <x-select name="game_master_id" required>
            @foreach ($masters as $master)<option value="{{ $master->id }}" @selected(old('game_master_id', $session->game_master_id) == $master->id)>{{ $master->name }}</option>@endforeach
        </x-select>
        @foreach (['starts_at' => 'Comienzo', 'ends_at' => 'Final'] as $field => $label)
            <x-label :for="$field">{{ $label }}</x-label>
            <x-input :name="$field" type="datetime-local" :value="$session->$field?->timezone('Europe/Madrid')->format('Y-m-d\TH:i')" required />
        @endforeach
        <x-label for="locale">Idioma de la partida</x-label>
        <x-select name="locale">
            <option value="es" @selected(old('locale', $session->locale) !== 'en')>Español</option>
            <option value="en" @selected(old('locale', $session->locale) === 'en')>Inglés</option>
        </x-select>
        <button class="button" type="submit">Guardar sesión</button>
    </form>
    <a href="{{ route('personal.sesiones.index') }}">Volver a agenda</a>
</x-layout>
