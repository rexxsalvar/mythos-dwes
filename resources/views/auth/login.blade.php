<x-layout title="Entrar">
    <div class="auth-grid">
        <div class="auth-intro"><p class="eyebrow">BIENVENIDO A MYTHOS</p><h1>Tu próxima historia<br>empieza aquí.</h1><p>Consulta tus reservas y los detalles de tu experiencia desde tu espacio personal.</p></div>
        <section class="panel auth-panel" aria-labelledby="form-title">
            <p class="eyebrow">TU CUENTA</p><h2 id="form-title">Entrar en Mythos</h2><p class="muted">Introduce tu correo y contraseña.</p>
            <form method="POST" action="{{ route('login.store') }}" class="form-stack">
                @csrf
                <div><x-label for="email">Correo electrónico</x-label><x-input name="email" type="email" autocomplete="username" maxlength="255" required autofocus /></div>
                <div><x-label for="password">Contraseña</x-label><x-input name="password" type="password" autocomplete="current-password" required /></div>
                <x-checkbox name="remember">Recordarme en este equipo</x-checkbox>
                <button class="button full-width" type="submit">Entrar</button>
            </form>
            <p class="form-footer">¿Todavía no tienes cuenta? <a href="{{ route('register') }}">Crear cuenta</a></p>
        </section>
    </div>
</x-layout>
