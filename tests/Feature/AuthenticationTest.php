<?php

use App\Models\User;
use Database\Seeders\AccessRolesSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(AccessRolesSeeder::class);
    $this->withoutVite();
});

test('guests can open the public home and authentication forms', function () {
    $this->get(route('home'))->assertOk()->assertSee('La aventura empieza');
    $this->get(route('login'))->assertOk()->assertSee('Recordarme en este equipo');
    $this->get(route('register'))->assertOk()->assertSee('Repetir contraseña');
});

test('registration hashes passwords and ignores injected roles and account attributes', function () {
    $this->post(route('register.store'), [
        'name' => 'Cliente de prueba', 'email' => 'NUEVA@example.test', 'phone' => '+34 600 123 456',
        'password' => 'Prueba2026', 'password_confirmation' => 'Prueba2026',
        'role' => 'administrador', 'roles' => ['administrador'], 'is_active' => false,
        'email_verified_at' => now(), 'permissions' => ['administration.view'],
    ])->assertRedirect(route('dashboard'));
    $user = User::where('email', 'nueva@example.test')->firstOrFail();
    $this->assertAuthenticatedAs($user);
    expect(Hash::check('Prueba2026', $user->password))->toBeTrue()
        ->and($user->getRoleNames()->all())->toBe(['cliente'])
        ->and($user->can('administration.view'))->toBeFalse()
        ->and($user->email_verified_at)->toBeNull()->and($user->is_active)->toBeTrue();
    $this->get(route('dashboard'))->assertOk();
    $this->get(route('administration.index'))->assertForbidden();
});

test('registration validates required fields phone confirmation and password strength', function () {
    $this->from(route('register'))->post(route('register.store'), [
        'name' => '', 'email' => 'incorrecto', 'phone' => 'abc',
        'password' => 'corta', 'password_confirmation' => 'distinta',
    ])->assertRedirect(route('register'))->assertSessionHasErrors(['name', 'email', 'phone', 'password']);
    expect(User::count())->toBe(0);
    $this->assertGuest();
});

test('registration rejects duplicate emails after normalization', function () {
    User::factory()->create(['email' => 'cliente@example.test']);
    $this->post(route('register.store'), [
        'name' => 'Duplicado', 'email' => 'CLIENTE@example.test',
        'password' => 'Prueba2026', 'password_confirmation' => 'Prueba2026',
    ])->assertSessionHasErrors('email');
    expect(User::count())->toBe(1);
});

test('active users can log in with remember me and a renewed session identifier', function () {
    $user = User::factory()->create(['password' => Hash::make('Prueba2026')])->assignRole('cliente');
    $this->get(route('login'));
    $previousId = session()->getId();
    $this->post(route('login.store'), [
        'email' => strtoupper($user->email), 'password' => 'Prueba2026', 'remember' => '1',
    ])->assertRedirect(route('dashboard'))->assertCookie(auth()->guard('web')->getRecallerName());
    $this->assertAuthenticatedAs($user);
    expect(session()->getId())->not->toBe($previousId);
});

test('invalid credentials and inactive accounts cannot log in', function (bool $active, string $password) {
    $user = User::factory()->create(['password' => Hash::make('Prueba2026'), 'is_active' => $active]);
    $this->post(route('login.store'), ['email' => $user->email, 'password' => $password])->assertSessionHasErrors('email');
    $this->assertGuest();
})->with([[true, 'incorrecta'], [false, 'Prueba2026']]);

test('login validates malformed values and never flashes passwords', function () {
    $this->post(route('login.store'), ['email' => 'incorrecto', 'password' => 'Secreta2026', 'remember' => 'incorrecto'])
        ->assertSessionHasErrors(['email', 'remember'])->assertSessionMissing('_old_input.password');
    $this->assertGuest();
});

test('login limits failed attempts and allows retry after the waiting period', function () {
    $this->freezeTime();
    $user = User::factory()->create()->assignRole('cliente');
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'incorrecta']);
    }
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors(['email' => 'Demasiados intentos. Inténtalo de nuevo en 60 segundos.']);
    $this->assertGuest();
    $this->travel(61)->seconds();
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('authenticated users cannot register another account or reopen guest forms', function () {
    $user = User::factory()->create()->assignRole('cliente');
    $this->actingAs($user)->get(route('register'))->assertRedirect(route('dashboard'));
    $this->get(route('login'))->assertRedirect(route('dashboard'));
    $this->post(route('register.store'), [])->assertRedirect(route('dashboard'));
    expect(User::count())->toBe(1);
});

test('logout uses POST and invalidates authentication', function () {
    $user = User::factory()->create()->assignRole('cliente');
    $this->actingAs($user)->get('/salir')->assertMethodNotAllowed();
    $this->post(route('logout'))->assertRedirect(route('home'));
    $this->assertGuest();
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('deactivating an existing session signs the user out', function () {
    $user = User::factory()->create(['is_active' => false])->assignRole('cliente');
    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('login'))->assertSessionHasErrors('email');
    $this->assertGuest();
    $this->actingAs($user)->getJson(route('profile.edit'))->assertForbidden();
    $this->assertGuest();
});
