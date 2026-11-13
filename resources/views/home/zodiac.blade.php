<section id="dr-zodiac" class="zodiac-journey" aria-labelledby="zodiac-title" data-zodiac-journey data-sequence-version="door-v4" data-sequence-manifest="sequence-door-v4.json" data-frames="{{ asset('assets/mythos/zodiac') }}">
    <div class="zodiac-stage" aria-hidden="true">
        <picture class="zodiac-poster">
            <source media="(max-width: 760px)" srcset="{{ asset('assets/mythos/zodiac/poster-mobile.webp') }}">
            <img src="{{ asset('assets/mythos/zodiac/poster.webp') }}" width="1280" height="800" alt="" loading="lazy" decoding="async">
        </picture>
        <canvas class="zodiac-canvas" width="1920" height="1200"></canvas>
    </div>

    <header class="zodiac-opening">
        <div class="zodiac-intro">
            <p class="zodiac-location">The Shadow · El Palmar</p>
            <h2 id="zodiac-title">Dr.<br><span>Zodiac</span></h2>
            <p class="zodiac-lead">Los pacientes entraban. Nadie los vio salir.</p>
            <a class="zodiac-skip" href="#zodiac-details">Ir a la ficha de la sala <x-icon name="down" /></a>
        </div>
    </header>

    <div class="zodiac-evidence" aria-hidden="true"></div>

    <article id="zodiac-details" class="zodiac-arrival" aria-labelledby="zodiac-mission-title" tabindex="-1">
        <div class="zodiac-brief">
            <h3 id="zodiac-mission-title">Investigad la clínica clandestina.</h3>
            <p>Formáis parte de la brigada científica. Descubrid quién está detrás de las desapariciones de pacientes en una clínica clandestina de El Palmar.</p>
            <dl class="zodiac-facts">
                <div><dt>Equipo</dt><dd>2–6 <span>personas</span></dd></div>
                <div><dt>Tiempo</dt><dd>60 <span>minutos</span></dd></div>
                <div><dt>Dificultad</dt><dd>Media</dd></div>
            </dl>
            <a class="zodiac-official" href="https://theshadowescaperoom.com/el-juego/" rel="external">Conocer Dr. Zodiac <x-icon /></a>
            <p class="zodiac-destination">En la web oficial de The Shadow.</p>
            <a class="experience-next" href="#el-virus"><span>Siguiente experiencia</span>El Virus <x-icon name="down" /></a>
        </div>
        <p class="zodiac-art-note">Ambientación artística inspirada en Dr. Zodiac.</p>
    </article>
</section>
