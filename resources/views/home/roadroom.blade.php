<section id="road-room" class="roadroom" aria-labelledby="roadroom-title" data-roadroom>
    <div class="roadroom-stage">
        <div class="roadroom-scene" aria-hidden="true">
            <img class="roadroom-trailer" src="{{ asset('assets/mythos/roadroom/remolque.webp') }}" alt="" width="1448" height="1086" loading="lazy" decoding="async">
            @foreach (['ascensor', 'cabina-dj'] as $room)
                <div class="roadroom-portal roadroom-portal-{{ $room }}">
                    <div class="roadroom-cabin">
                        <div class="roadroom-cabin-image">
                            <img src="{{ asset("assets/mythos/roadroom/{$room}.webp") }}" alt="" width="1024" height="1536" loading="lazy" decoding="async">
                            @if ($room === 'cabina-dj')
                                <i class="roadroom-meter roadroom-meter-upper"></i>
                                <i class="roadroom-meter roadroom-meter-lower"></i>
                            @endif
                        </div>
                    </div>
                    <div class="roadroom-door"></div>
                </div>
            @endforeach
        </div>
        <header class="roadroom-intro">
            <p>06 · Escape portátil · Murcia</p>
            <h2 id="roadroom-title">ROAD<br>ROOM<span>.</span></h2>
        </header>
        <a class="roadroom-skip" href="#roadroom-details">Ir a la ficha de Road Room <x-icon name="down" /></a>
        <div class="roadroom-guide" aria-hidden="true">
            <span data-roadroom-step>Desplaza para abrir</span>
            <span data-roadroom-step>01 · Ascensor · 08:00</span>
            <span data-roadroom-step>Dos juegos en el mismo remolque</span>
            <span data-roadroom-step>02 · Cabina del DJ · 08:00</span>
            <x-icon name="down" />
        </div>
    </div>
    <article id="roadroom-details" class="roadroom-details" tabindex="-1" aria-labelledby="roadroom-details-title">
        <p class="roadroom-label">Empresas · Celebraciones · Eventos</p>
        <h3 id="roadroom-details-title">El juego llega<br>hasta vosotros.</h3>
        <p>Dos experiencias de ocho minutos viajan en el mismo remolque. A la derecha, escapad de un ascensor. A la izquierda, resolved el reto de la cabina del DJ.</p>
        <a href="https://roadroom.es/" rel="external">Conocer Road Room en su web oficial <x-icon /></a>
        <small>Foto del remolque retocada e interiores recreados para esta portada.</small>
        <nav class="roadroom-next" aria-label="Continuar el recorrido">
            <a href="#experience-index">Elegid vuestra próxima aventura <x-icon name="down" /></a>
        </nav>
    </article>
</section>
