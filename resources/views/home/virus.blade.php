<section id="el-virus" class="virus-journey" aria-labelledby="virus-title" data-experience-sequence data-sequence-version="optical-glass-4" data-frames="{{ asset('assets/mythos/virus') }}">
    <div class="virus-stage" aria-hidden="true">
        <picture class="virus-poster">
            <source media="(max-width: 760px)" srcset="{{ asset('assets/mythos/virus/poster-mobile.webp').'?v=optical-glass-4' }}">
            <img src="{{ asset('assets/mythos/virus/poster.webp').'?v=optical-glass-4' }}" width="1600" height="1000" alt="" loading="lazy" decoding="async">
        </picture>
        <canvas class="virus-canvas" data-sequence-canvas width="1600" height="1000"></canvas>
        <div class="virus-track"><span data-sequence-step="sample" data-sequence-start="0">Muestra</span><span data-sequence-step="analysis" data-sequence-start="0.4">Compuerta</span><span data-sequence-step="laboratory" data-sequence-start="0.69">Laboratorio</span><i></i></div>
    </div>

    <header class="virus-opening">
        <div class="virus-intro">
            <p class="virus-location">Una misión del CNI <span>Enigmatic · Murcia centro</span></p>
            <p class="virus-overline"><span aria-hidden="true">☣</span> Sigue el rastro de la cura</p>
            <h2 id="virus-title"><span>El</span>Virus</h2>
            <p class="virus-lead">La cura existe.<br>Su creador ha desaparecido.</p>
            <a class="virus-skip" href="#virus-details">Ir a la ficha de la sala <x-icon name="down" /></a>
        </div>
        <p class="virus-scroll" aria-hidden="true"><span></span> Sigue la muestra</p>
    </header>

    <div class="virus-analysis" aria-hidden="true">
        <p><span>El tiempo cuenta</span>48 horas sin él.<br>60 minutos para encontrarlo.</p>
    </div>

    <article id="virus-details" class="virus-arrival" aria-labelledby="virus-mission-title" tabindex="-1">
        <div class="virus-brief">
            <p class="virus-overline">Aceptad la misión</p>
            <h3 id="virus-mission-title">El CNI<br><span>os necesita.</span></h3>
            <p>Un virus mortal se está extendiendo. El Dr. Luis Fuentes Ferrer es el único que conoce la cura y lleva 48 horas desaparecido. Vuestro equipo debe encontrarlo y recuperar la cura antes de que se declare el estado de emergencia.</p>
            <dl class="virus-facts">
                <div><dt>Equipo</dt><dd>2–6 <span>personas</span></dd></div>
                <div><dt>Tiempo</dt><dd>60 <span>minutos</span></dd></div>
                <div><dt>Dificultad</dt><dd>Media</dd></div>
            </dl>
            <a class="virus-official" href="https://enigmatic.es/el-juego/" rel="external">Conocer El Virus <x-icon /></a>
            <p class="virus-destination">En la web oficial de Enigmatic.</p>
            <a class="experience-next" href="#el-virus-2"><span>Siguiente experiencia</span>El Virus 2 <x-icon name="down" /></a>
        </div>
        <p class="virus-art-note">Ambientación artística inspirada en El Virus.</p>
    </article>
</section>
