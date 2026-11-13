<x-layout title="Administración">
    <div class="page-heading"><p class="eyebrow">VISTA GENERAL</p><h1>Administración</h1><p class="muted">Resumen de registros y perfiles de acceso de Mythos.</p></div>
    <div class="stats-grid">@foreach($counts as $label => $count)<div class="panel stat"><span class="muted">{{ $label }}</span><strong>{{ $count }}</strong></div>@endforeach</div>
    <section class="panel"><h2>Perfiles de acceso</h2><dl class="details">@foreach($roles as $role)<div><dt>{{ config('mythos.role_labels.'.$role->name, $role->name) }}</dt><dd>{{ $role->users_count }} {{ $role->users_count === 1 ? 'cuenta' : 'cuentas' }}</dd></div>@endforeach</dl></section>
    <section class="panel"><h2>Catálogo y agenda</h2>
        <p><a href="{{ route('administration.experiences.index') }}">Gestionar experiencias</a></p>
        <p><a href="{{ route('administration.rooms.index') }}">Gestionar salas</a></p>
        <p><a href="{{ route('administration.sessions.create') }}">Programar sesión</a></p>
        <p><a href="{{ route('personal.sesiones.index') }}">Consultar agenda</a></p>
    </section>
    <section class="panel"><h2>Revisión de reservas</h2>
        @foreach ($errors->get('operations') as $error)<p class="field-error" role="alert">{{ $error }}</p>@endforeach
        <form method="POST" action="{{ route('administration.operations.expire') }}" data-submit>
            @csrf<button class="button" type="submit">Caducar pendientes vencidas</button>
        </form>
        <form method="POST" action="{{ route('administration.operations.review') }}" data-submit>
            @csrf<button class="button" type="submit">Detectar reservas afectadas</button>
        </form>
    </section>
    <section class="panel"><h2>Resumen de actividad mensual</h2>
        <form method="POST" action="{{ route('administration.operations.monthly') }}" class="narrow-panel" novalidate>
            @csrf
            <x-label for="month">Mes</x-label>
            <x-input name="month" type="month" :value="now('Europe/Madrid')->format('Y-m')" required />
            <x-label for="room_id">Sala</x-label>
            <x-select name="room_id"><option value="">Todas las salas</option>
                @foreach ($rooms as $room)<option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>{{ $room->name }}</option>@endforeach
            </x-select>
            <button class="button" type="submit">Descargar datos de actividad</button>
        </form>
    </section>
</x-layout>
