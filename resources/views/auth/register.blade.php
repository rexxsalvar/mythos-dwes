<x-layout title="Crear cuenta">
    <div class="auth-grid">
        <div class="auth-intro"><p class="eyebrow">FORMA PARTE DE LA AVENTURA</p><h1>Un lugar para<br>tus experiencias.</h1><p>Crea tu cuenta de cliente para consultar tus reservas y mantener tus datos de contacto al día.</p></div>
        <section class="panel auth-panel" aria-labelledby="form-title">
            <p class="eyebrow">NUEVA CUENTA</p><h2 id="form-title">Crear cuenta</h2>
            <form method="POST" action="{{ route('register.store') }}" class="form-stack">
                @csrf
                <div><x-label for="name">Nombre</x-label><x-input name="name" autocomplete="name" maxlength="255" required /></div>
                <div><x-label for="email">Correo electrónico</x-label><x-input name="email" type="email" autocomplete="email" maxlength="255" required /></div>
                <div><x-label for="phone">Teléfono <span class="muted">(opcional)</span></x-label><x-input name="phone" type="tel" autocomplete="tel" maxlength="30" /></div>
                <div><x-label for="password">Contraseña</x-label><x-input name="password" type="password" autocomplete="new-password" minlength="8" required /><p class="field-hint">Al menos 8 caracteres, con letras y números.</p></div>
                <div><x-label for="password_confirmation">Repetir contraseña</x-label><x-input name="password_confirmation" type="password" autocomplete="new-password" required /></div>
                <button class="button full-width" type="submit">Crear mi cuenta</button>
            </form>
            <p class="form-footer">¿Ya tienes cuenta? <a href="{{ route('login') }}">Entrar</a></p>
        </section>
    </div>
</x-layout>
