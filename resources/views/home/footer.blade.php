<footer id="experience-index" class="home-footer" aria-labelledby="experience-index-title" tabindex="-1">
    <div class="home-footer-intro">
        <p class="eyebrow">MYTHOS · MURCIA</p>
        <h2 id="experience-index-title">Vuestra próxima<br><span>historia os espera.</span></h2>
        <a class="text-link" href="#home-experiences" data-home-nav-open="experiences">Elegir otra experiencia <x-icon /></a>
    </div>
    <nav class="home-footer-experiences" aria-label="Fichas de las experiencias">
        <a href="#expedition-details"><span>01</span>Expedición Maldita<x-icon /></a>
        <a href="#zodiac-details"><span>02</span>Dr. Zodiac<x-icon /></a>
        <a href="#virus-details"><span>03</span>El Virus<x-icon /></a>
        <a href="#solanum-details"><span>04</span>El Virus 2<x-icon /></a>
        <a href="#legends-details"><span>05</span>Legends<x-icon /></a>
        <a href="#roadroom-details"><span>06</span>Road Room<x-icon /></a>
    </nav>
    <div class="home-footer-bottom">
        <a class="footer-brand" href="{{ route('home') }}" aria-label="Mythos, inicio">
            <img src="{{ asset('assets/mythos/wordmark.webp') }}" width="255" height="35" alt="MYTHOS" loading="lazy">
            <span>ESCAPE ROOM</span>
        </a>
        <p>Un equipo. Una aventura.</p>
        @auth
            <a href="{{ route('dashboard') }}">Ir a mi espacio <x-icon /></a>
        @else
            <a href="{{ route('login') }}">Entrar en mi espacio <x-icon /></a>
            <a href="{{ route('register') }}">Crear una cuenta</a>
        @endauth
    </div>
</footer>
