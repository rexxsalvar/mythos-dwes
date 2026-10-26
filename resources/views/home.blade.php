<x-layout>
    <section class="hero">
        <div>
            <p class="eyebrow">MYTHOS · MURCIA</p>
            <h1>La aventura empieza<br>con tu equipo.</h1>
            <p class="hero-copy">Un misterio por resolver. Una experiencia para recordar. Entra en tu espacio y consulta los detalles de tus partidas.</p>
            <div class="actions">
                @auth
                    <a class="button" href="{{ route('dashboard') }}">Ir a mi espacio <span aria-hidden="true">↗</span></a>
                @else
                    <a class="button" href="{{ route('login') }}">Entrar en mi espacio <span aria-hidden="true">↗</span></a>
                    <a class="text-link" href="{{ route('register') }}">Crear una cuenta</a>
                @endauth
            </div>
        </div>
        <div class="hero-art" aria-hidden="true"><div class="orbit orbit-one"></div><div class="orbit orbit-two"></div><div class="hero-symbol">M</div><span class="art-caption">DESCUBRE LO EXTRAORDINARIO</span></div>
    </section>
</x-layout>
