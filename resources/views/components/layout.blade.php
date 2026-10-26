@props(['title' => 'Tu próxima aventura'])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Mythos</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#contenido">Saltar al contenido</a>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="Mythos, inicio"><span class="brand-mark" aria-hidden="true">M</span> MYTHOS</a>
            <nav aria-label="Navegación principal">
                @auth
                    <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Mi espacio</a>
                    @can('reservations.view-own')<a href="{{ route('cliente.reservas.index') }}">Mis reservas</a>@endcan
                    @can('reservations.view-all')<a href="{{ route('personal.reservas.index') }}">Reservas</a>@endcan
                    @if(auth()->user()->can('sessions.view-all') || auth()->user()->can('sessions.view-assigned'))<a href="{{ route('personal.sesiones.index') }}">Agenda</a>@endif
                    @if(auth()->user()->can('incidents.view-all') || auth()->user()->can('incidents.view-assigned'))<a href="{{ route('mantenimiento.incidencias.index') }}">Incidencias</a>@endif
                    @can('administration.view')<a href="{{ route('administration.index') }}">Administración</a>@endcan
                    @can('profile.update-own')<a href="{{ route('profile.edit') }}">Mi perfil</a>@endcan
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-button" type="submit">Salir</button></form>
                @else
                    <a href="{{ route('login') }}">Entrar</a>
                    <a class="nav-accent" href="{{ route('register') }}">Crear cuenta</a>
                @endauth
            </nav>
        </div>
    </header>
    <main id="contenido" class="page-shell">
        @if(session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
        {{ $slot }}
    </main>
    <footer class="site-footer"><span>MYTHOS · ESCAPE ROOM</span><span>Murcia · Un equipo, una aventura.</span></footer>
</body>
</html>
