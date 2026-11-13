<x-layout variant="home">
    <div id="expedicion-maldita" class="relic-journey" aria-labelledby="expedition-title" tabindex="-1" data-relic-journey data-frames="{{ asset('assets/mythos/expedition') }}">
        <div class="relic-stage" aria-hidden="true">
            <div class="relic-visual">
                <picture class="relic-poster">
                    <source media="(max-width: 760px)" srcset="{{ asset('assets/mythos/expedition/poster-mobile.webp') }}">
                    <img src="{{ asset('assets/mythos/expedition/poster.webp') }}" width="1280" height="800" alt="" fetchpriority="high" decoding="async">
                </picture>
                <canvas class="relic-canvas" width="1280" height="800"></canvas>
            </div>
            <div class="relic-margin"><span>EXPEDICIÓN MALDITA</span><span class="relic-progress"></span></div>
        </div>
        <section class="hero">
            <div class="hero-content">
                <p class="eyebrow">MYTHOS · EL PALMAR</p>
                <p class="expedition-name" id="expedition-title">01 / 06 · Expedición Maldita</p>
                <h1>La aventura empieza <span>con tu equipo.</span></h1>
                <p class="hero-copy">Un encargo oscuro os lleva hasta una tumba faraónica. Reunid a vuestro equipo de arqueólogos y seguid al báculo.</p>
                <div class="actions">
                    <a class="button button--expedition" href="#expedition-details">Ver la expedición <x-icon name="pyramid" /></a>
                    <a class="text-link" href="#home-experiences" data-home-nav-open="experiences">Elegir experiencia <x-icon name="down" /></a>
                </div>
                <p class="hero-scroll"><span></span> Sigue al báculo <x-icon name="down" /></p>
            </div>
        </section>
        <section class="relic-chapter" aria-labelledby="mechanism-title">
            <div class="relic-chapter-copy">
                <p class="eyebrow">EL ENCARGO OS ESPERA</p>
                <h2 id="mechanism-title">El misterio está<br><span>al otro lado.</span></h2>
            </div>
        </section>
        <article id="expedition-details" class="story" aria-labelledby="story-title" tabindex="-1">
            <div class="story-content">
                <p class="eyebrow">EXPEDICIÓN MALDITA</p>
                <h2 id="story-title">Una tumba.<br><span>Un encargo que cumplir.</span></h2>
                <div class="story-copy">
                    <p>Viajáis a El Cairo junto a un equipo de arqueólogos. Un personaje misterioso os obliga a adentraros en una tumba faraónica para cumplir su misión. Vuestra observación, ingenio y trabajo en equipo decidirán cómo termina la expedición.</p>
                    <dl class="expedition-facts">
                        <div><dt>Equipo</dt><dd>2–6 <span>personas</span></dd></div>
                        <div><dt>Tiempo</dt><dd>75 <span>minutos</span></dd></div>
                        <div><dt>Dificultad</dt><dd>Alta</dd></div>
                    </dl>
                    <a class="button button--expedition" href="https://mythosmurcia.es/expedicion-maldita/" rel="external">Conocer Expedición Maldita <x-icon /></a>
                    <p class="expedition-destination">En la web oficial de Mythos.</p>
                    <p class="expedition-art-note">Ambientación artística inspirada en Expedición Maldita.</p>
                    <a class="experience-next" href="#dr-zodiac"><span>Siguiente experiencia</span>Dr. Zodiac <x-icon name="down" /></a>
                </div>
            </div>
        </article>
    </div>
    @include('home.zodiac')
    @include('home.virus')
    @include('home.solanum')
    @include('home.legends')
    @include('home.roadroom')
</x-layout>
