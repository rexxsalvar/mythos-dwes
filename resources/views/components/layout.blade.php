@props(['title' => 'Tu próxima aventura', 'variant' => 'app'])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Mythos</title>
    <meta name="theme-color" content="#0b0b10">
    <link rel="icon" type="image/webp" href="{{ asset('assets/mythos/symbol.webp') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="page-{{ $variant }}">
    <a class="skip-link" href="#contenido">Saltar al contenido</a>
    @if($variant === 'home')
        @include('home.navigation')
    @else
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="Mythos, inicio">
                <img class="brand-symbol" src="{{ asset('assets/mythos/symbol.webp') }}" width="43" height="43" alt="">
                <img class="brand-wordmark" src="{{ asset('assets/mythos/wordmark.webp') }}" width="255" height="35" alt="MYTHOS">
            </a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="main-navigation" data-menu-toggle hidden disabled>
                <x-icon name="menu" /> <span>Menú</span>
            </button>
            <nav id="main-navigation" class="site-nav" aria-label="Navegación principal">
                @auth
                    <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Mi espacio</a>
                    @can('reservations.view-own')<a href="{{ route('cliente.reservas.index') }}" @if(request()->routeIs('cliente.reservas.*')) aria-current="page" @endif>Mis reservas</a>@endcan
                    @can('reservations.view-all')<a href="{{ route('personal.reservas.index') }}" @if(request()->routeIs('personal.reservas.*')) aria-current="page" @endif>Reservas</a>@endcan
                    @if(auth()->user()->can('sessions.view-all') || auth()->user()->can('sessions.view-assigned'))<a href="{{ route('personal.sesiones.index') }}" @if(request()->routeIs('personal.sesiones.*')) aria-current="page" @endif>Agenda</a>@endif
                    @if(auth()->user()->can('incidents.view-all') || auth()->user()->can('incidents.view-assigned'))<a href="{{ route('mantenimiento.incidencias.index') }}" @if(request()->routeIs('mantenimiento.incidencias.*')) aria-current="page" @endif>Incidencias</a>@endif
                    @can('administration.view')<a href="{{ route('administration.index') }}" @if(request()->routeIs('administration.*')) aria-current="page" @endif>Administración</a>@endcan
                    @can('profile.update-own')<a href="{{ route('profile.edit') }}" @if(request()->routeIs('profile.*')) aria-current="page" @endif>Mi perfil</a>@endcan
                    <form method="POST" action="{{ route('logout') }}" novalidate>@csrf<button class="nav-button" type="submit">Salir</button></form>
                @else
                    <a href="{{ route('login') }}" @if(request()->routeIs('login')) aria-current="page" @endif>Entrar</a>
                    <a class="nav-accent" href="{{ route('register') }}" @if(request()->routeIs('register')) aria-current="page" @endif>Crear cuenta</a>
                @endauth
            </nav>
        </div>
    </header>
    @endif
    <main id="contenido" class="page-shell">
        @if(session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
        {{ $slot }}
    </main>
    @if($variant === 'home')
        @include('home.footer')
    @else
    <footer class="site-footer">
        <a class="footer-brand" href="{{ route('home') }}" aria-label="Mythos, inicio">
            <img src="{{ asset('assets/mythos/wordmark.webp') }}" width="255" height="35" alt="MYTHOS" loading="lazy">
            <span>ESCAPE ROOM</span>
        </a>
        <span>Murcia · Un equipo, una aventura.</span>
    </footer>
    @endif
</body>
</html>
