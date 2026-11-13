<x-layout title="Mi espacio">
    <div class="page-heading"><p class="eyebrow">TU ESPACIO EN MYTHOS</p><h1>Hola, {{ $user->name }}.</h1><p class="muted">Desde aquí puedes acceder a tus consultas y mantener tus datos al día.</p></div>
    <div class="role-list">@foreach($user->getRoleNames() as $role)<span class="badge">{{ config('mythos.role_labels.'.$role, $role) }}</span>@endforeach</div>
    <div class="card-grid">
        <x-area-link :href="route('experiences.index')" title="Experiencias" icon="ticket" action="Ver disponibilidad">Consulta las tarifas y las próximas sesiones.</x-area-link>
        @can('reservations.view-own')
            <x-area-link :href="route('cliente.reservas.index')" title="Mis reservas" icon="ticket" action="Consultar reservas">Los detalles de tus partidas, en un solo lugar.</x-area-link>
        @endcan
        @can('reservations.view-all')
            <x-area-link :href="route('personal.reservas.index')" title="Reservas" icon="ticket" action="Ver reservas">Consulta las reservas para atender a los grupos.</x-area-link>
        @endcan
        @if($user->can('sessions.view-assigned') || $user->can('sessions.view-all'))
            <x-area-link :href="route('personal.sesiones.index')" title="Agenda de partidas" icon="calendar" action="Abrir agenda">Consulta las sesiones y filtra por fecha o sala.</x-area-link>
        @endif
        @if($user->can('incidents.view-assigned') || $user->can('incidents.view-all'))
            <x-area-link :href="route('mantenimiento.incidencias.index')" title="Incidencias" icon="tool" action="Ver incidencias">Consulta las incidencias y sus actuaciones.</x-area-link>
        @endif
        @can('administration.view')
            <x-area-link :href="route('administration.index')" title="Administración" icon="overview" action="Consultar resumen">Una vista general de la actividad y los perfiles.</x-area-link>
        @endcan
        <x-area-link :href="route('profile.edit')" title="Mi perfil" icon="profile" action="Editar mis datos">Revisa tu nombre, contacto e idioma preferido.</x-area-link>
    </div>
</x-layout>
