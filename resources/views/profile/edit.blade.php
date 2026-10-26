<x-layout title="Mi perfil">
    <div class="page-heading"><p class="eyebrow">MI CUENTA</p><h1>Tus datos, al día.</h1><p class="muted">Revisa cómo podemos contactar contigo.</p></div>
    <section class="panel narrow-panel">
        <form method="POST" action="{{ route('profile.update') }}" class="form-stack">
            @csrf @method('PATCH')
            <div><x-label for="name">Nombre</x-label><x-input name="name" :value="$user->name" autocomplete="name" maxlength="255" required /></div>
            <div><x-label for="email">Correo electrónico</x-label><x-input name="email" type="email" :value="$user->email" autocomplete="email" maxlength="255" required /></div>
            <div><x-label for="phone">Teléfono <span class="muted">(opcional)</span></x-label><x-input name="phone" type="tel" :value="$user->phone" autocomplete="tel" maxlength="30" /></div>
            <div><x-label for="preferred_locale">Idioma preferido</x-label><x-select name="preferred_locale" required><option value="es" @selected(old('preferred_locale', $user->preferred_locale) === 'es')>Español</option><option value="en" @selected(old('preferred_locale', $user->preferred_locale) === 'en')>Inglés</option></x-select></div>
            <div class="actions"><button class="button" type="submit">Guardar mis datos</button><a class="text-link" href="{{ route('dashboard') }}">Volver a mi espacio</a></div>
        </form>
    </section>
</x-layout>
