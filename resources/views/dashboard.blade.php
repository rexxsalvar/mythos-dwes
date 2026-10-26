<x-layout title="Mi espacio">
    <div class="page-heading"><p class="eyebrow">TU ESPACIO EN MYTHOS</p><h1>Hola, {{ $user->name }}.</h1><p class="muted">Desde aquí puedes acceder a tus consultas y mantener tus datos al día.</p></div>
    <div class="role-list">@foreach($user->getRoleNames() as $role)<span class="badge">{{ config('mythos.role_labels.'.$role, $role) }}</span>@endforeach</div>
    <div class="card-grid">
        @can('reservations.view-own')<a class="feature-card" href="{{ route('cliente.reservas.index') }}"><span class="card-number">01</span><h2>Mis reservas</h2><p>Los detalles de tus partidas, en un solo lugar.</p><span class="text-link">Consultar reservas ↗</span></a>@endcan
        @can('reservations.view-all')<a class="feature-card" href="{{ route('personal.reservas.index') }}"><span class="card-number">R</span><h2>Reservas</h2><p>Consulta las reservas para atender a los grupos.</p><span class="text-link">Ver reservas ↗</span></a>@endcan
        @if($user->can('sessions.view-assigned') || $user->can('sessions.view-all'))<a class="feature-card" href="{{ route('personal.sesiones.index') }}"><span class="card-number">A</span><h2>Agenda de partidas</h2><p>Consulta las sesiones y filtra por fecha o sala.</p><span class="text-link">Abrir agenda ↗</span></a>@endif
        @if($user->can('incidents.view-assigned') || $user->can('incidents.view-all'))<a class="feature-card" href="{{ route('mantenimiento.incidencias.index') }}"><span class="card-number">I</span><h2>Incidencias</h2><p>Consulta las incidencias y sus actuaciones.</p><span class="text-link">Ver incidencias ↗</span></a>@endif
        @can('administration.view')<a class="feature-card" href="{{ route('administration.index') }}"><span class="card-number">G</span><h2>Administración</h2><p>Una vista general de la actividad y los perfiles.</p><span class="text-link">Consultar resumen ↗</span></a>@endcan
        <a class="feature-card" href="{{ route('profile.edit') }}"><span class="card-number">P</span><h2>Mi perfil</h2><p>Revisa tu nombre, contacto e idioma preferido.</p><span class="text-link">Editar mis datos ↗</span></a>
    </div>
</x-layout>
