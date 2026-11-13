@php
    $homeChapters = [
        ['id' => 'expedicion-maldita', 'details' => 'expedition-details', 'name' => 'Expedición Maldita', 'theme' => 'Aventura egipcia', 'image' => 'expedition/poster.webp'],
        ['id' => 'dr-zodiac', 'details' => 'zodiac-details', 'name' => 'Dr. Zodiac', 'theme' => 'Investigación', 'image' => 'zodiac/poster.webp'],
        ['id' => 'el-virus', 'details' => 'virus-details', 'name' => 'El Virus', 'theme' => 'Laboratorio', 'image' => 'virus/poster.webp?v=optical-glass-4'],
        ['id' => 'el-virus-2', 'details' => 'solanum-details', 'name' => 'El Virus 2', 'theme' => 'Programa Solanum', 'image' => 'solanum/poster.webp?v=solanum-2'],
        ['id' => 'legends', 'details' => 'legends-details', 'name' => 'Legends', 'theme' => 'Al aire libre', 'image' => 'legends/banco.webp'],
        ['id' => 'road-room', 'details' => 'roadroom-details', 'name' => 'Road Room', 'theme' => 'Escape portátil', 'image' => 'roadroom/remolque.webp'],
    ];
@endphp

<header class="home-navigation" data-home-navigation>
    <div class="home-navigation-bar">
        <a class="home-navigation-brand" href="{{ route('home') }}" aria-label="Mythos, inicio">
            <img class="home-navigation-symbol" src="{{ asset('assets/mythos/symbol.webp') }}" width="43" height="43" alt="">
            <img class="home-navigation-wordmark" src="{{ asset('assets/mythos/wordmark.webp') }}" width="255" height="35" alt="MYTHOS">
        </a>
        <button class="home-navigation-selector" type="button" aria-expanded="false" aria-controls="home-experiences" data-home-nav-toggle="experiences" hidden>
            <span class="home-navigation-number" data-home-current-number aria-hidden="true">01</span>
            <span class="home-navigation-selector-copy">
                <span class="home-navigation-label">Experiencias</span>
                <span class="home-navigation-current" data-home-current-name>Expedición Maldita</span>
            </span>
            <x-icon name="down" />
        </button>
        <button class="home-navigation-account" type="button" aria-expanded="false" aria-controls="home-account" data-home-nav-toggle="account" hidden>
            <x-icon name="profile" />
            <span>@auth Mi espacio @else Cuenta @endauth</span>
        </button>
    </div>

    <nav id="home-experiences" class="home-navigation-panel home-navigation-experiences" aria-label="Experiencias de Mythos" data-home-nav-panel="experiences">
        <div class="home-navigation-panel-inner">
            <div class="home-navigation-index">
                <div class="home-navigation-panel-heading">
                    <h2>Seis formas de entrar<br>en otra historia.</h2>
                    <span>MYTHOS · MURCIA</span>
                </div>
                <ol class="home-navigation-chapters">
                    @foreach ($homeChapters as $chapter)
                        <li data-home-chapter="{{ $chapter['id'] }}" data-home-chapter-name="{{ $chapter['name'] }}" data-home-chapter-theme="{{ $chapter['theme'] }}" data-home-preview="{{ asset('assets/mythos/'.$chapter['image']) }}">
                            <span class="home-navigation-chapter-number" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="home-navigation-chapter-copy">
                                <span class="home-navigation-chapter-name">{{ $chapter['name'] }}</span>
                                <span class="home-navigation-chapter-theme">{{ $chapter['theme'] }}</span>
                            </div>
                            <div class="home-navigation-chapter-links">
                                <a href="#{{ $chapter['id'] }}" aria-label="Ver escena de {{ $chapter['name'] }}" data-home-scene-link>Ver escena <x-icon name="down" /></a>
                                <a href="#{{ $chapter['details'] }}" aria-label="Ver ficha de {{ $chapter['name'] }}">Ver ficha</a>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
            <figure class="home-navigation-preview" aria-hidden="true">
                <img src="{{ asset('assets/mythos/expedition/poster.webp') }}" alt="" width="1280" height="800" loading="lazy" decoding="async" data-home-preview-image>
                <figcaption>
                    <span data-home-preview-theme>Aventura egipcia</span>
                    <strong data-home-preview-name>Expedición Maldita</strong>
                </figcaption>
            </figure>
        </div>
    </nav>

    <nav id="home-account" class="home-navigation-panel home-navigation-account-panel" aria-label="Tu cuenta en Mythos" data-home-nav-panel="account">
        <div class="home-navigation-account-inner">
            <p class="home-navigation-label">Tu espacio en Mythos</p>
            <div class="home-navigation-account-links">
                @auth
                    <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Mi espacio <x-icon /></a>
                    @can('reservations.view-own')<a href="{{ route('cliente.reservas.index') }}" @if(request()->routeIs('cliente.reservas.*')) aria-current="page" @endif>Mis reservas <x-icon /></a>@endcan
                    @can('reservations.view-all')<a href="{{ route('personal.reservas.index') }}" @if(request()->routeIs('personal.reservas.*')) aria-current="page" @endif>Reservas <x-icon /></a>@endcan
                    @if(auth()->user()->can('sessions.view-all') || auth()->user()->can('sessions.view-assigned'))<a href="{{ route('personal.sesiones.index') }}" @if(request()->routeIs('personal.sesiones.*')) aria-current="page" @endif>Agenda <x-icon /></a>@endif
                    @if(auth()->user()->can('incidents.view-all') || auth()->user()->can('incidents.view-assigned'))<a href="{{ route('mantenimiento.incidencias.index') }}" @if(request()->routeIs('mantenimiento.incidencias.*')) aria-current="page" @endif>Incidencias <x-icon /></a>@endif
                    @can('administration.view')<a href="{{ route('administration.index') }}" @if(request()->routeIs('administration.*')) aria-current="page" @endif>Administración <x-icon /></a>@endcan
                    @can('profile.update-own')<a href="{{ route('profile.edit') }}" @if(request()->routeIs('profile.*')) aria-current="page" @endif>Mi perfil <x-icon /></a>@endcan
                    <form method="POST" action="{{ route('logout') }}" novalidate>
                        @csrf
                        <button type="submit">Salir <x-icon /></button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Entrar <x-icon /></a>
                    <a href="{{ route('register') }}">Crear cuenta <x-icon /></a>
                @endauth
            </div>
        </div>
    </nav>
</header>
