<?php

use App\Models\GameSession;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessRolesSeeder;

beforeEach(function () {
    $this->seed(AccessRolesSeeder::class);
    $this->withoutVite();
});

test('profile updates only the current user and ignores injected privileges', function () {
    $user = User::factory()->create()->assignRole('cliente');
    $other = User::factory()->create();
    $originalPassword = $user->password;
    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => 'Nombre actualizado', 'email' => 'PERFIL@example.test', 'phone' => '+34 600 111 222',
        'preferred_locale' => 'en', 'id' => $other->id, 'user_id' => $other->id,
        'roles' => ['administrador'], 'is_active' => false, 'password' => 'Cambiada2026',
    ])->assertRedirect(route('profile.edit'))->assertSessionHas('status', 'Tus datos se han guardado.');
    expect($user->fresh()->name)->toBe('Nombre actualizado')
        ->and($user->fresh()->email)->toBe('perfil@example.test')
        ->and($user->fresh()->preferred_locale)->toBe('en')
        ->and($user->fresh()->email_verified_at)->toBeNull()
        ->and($user->fresh()->is_active)->toBeTrue()
        ->and($user->fresh()->hasRole('administrador'))->toBeFalse()
        ->and($user->fresh()->password)->toBe($originalPassword)
        ->and($other->fresh()->name)->toBe($other->name);
});

test('profile validates unique emails phone format and supported languages', function () {
    $user = User::factory()->create()->assignRole('cliente');
    $other = User::factory()->create();
    $this->actingAs($user)->from(route('profile.edit'))->patch(route('profile.update'), [
        'name' => '', 'email' => $other->email, 'phone' => 'incorrecto', 'preferred_locale' => 'xx',
    ])->assertRedirect(route('profile.edit'))->assertSessionHasErrors(['name', 'email', 'phone', 'preferred_locale']);
    expect($user->fresh()->email)->toBe($user->email);
});

test('profile allows unchanged email and empty phone without resetting verification', function () {
    $user = User::factory()->create()->assignRole('cliente');
    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name, 'email' => $user->email, 'phone' => '', 'preferred_locale' => 'es',
    ])->assertSessionHasNoErrors();
    expect($user->fresh()->email_verified_at)->not->toBeNull()->and($user->fresh()->phone)->toBeNull();
});

test('components retain escaped old input and associate errors with the relevant field', function () {
    $user = User::factory()->create()->assignRole('cliente');
    $this->actingAs($user)->from(route('profile.edit'))->patch(route('profile.update'), [
        'name' => '<script>alert(1)</script>', 'email' => 'incorrecto', 'phone' => '', 'preferred_locale' => 'en',
    ])->assertSessionHasErrors('email');
    // Follow the redirect with the same browser cookie, including JSON session data.
    $this->withCookie(config('session.cookie'), session()->getId())->get(route('profile.edit'))->assertOk()
        ->assertSee('aria-invalid="true"', false)->assertSee('aria-describedby="email-error"', false)
        ->assertSee('value="&lt;script&gt;alert(1)&lt;/script&gt;"', false)
        ->assertDontSee('<script>alert(1)</script>', false)->assertSee('value="en" selected', false);
});

test('agenda validates dates intervals and rooms', function (array $filters, string $error) {
    $user = User::factory()->create()->assignRole('recepcion');
    $this->actingAs($user)->getJson(route('personal.sesiones.index', $filters))
        ->assertUnprocessable()->assertJsonValidationErrors($error);
})->with([
    'invalid date' => [['from' => '2026-02-30'], 'from'],
    'wrong format' => [['from' => 'ayer'], 'from'],
    'reversed range' => [['from' => '2026-10-20', 'until' => '2026-10-19'], 'until'],
    'unknown room' => [['room_id' => '999999'], 'room_id'],
    'invalid type' => [['room_id' => ['1']], 'room_id'],
]);

test('agenda uses Madrid calendar days over UTC storage and accepts either date alone', function () {
    $reception = User::factory()->create()->assignRole('recepcion');
    $sessions = collect(['2026-10-16 21:59:00', '2026-10-16 22:00:00', '2026-10-17 21:59:00', '2026-10-17 22:00:00'])
        ->map(fn ($date) => GameSession::factory()->create([
            'starts_at' => $date, 'ends_at' => CarbonImmutable::parse($date)->addMinutes(75),
        ]));
    $response = $this->actingAs($reception)->get(route('personal.sesiones.index', ['from' => '2026-10-17', 'until' => '2026-10-17']))->assertOk();
    expect($response->viewData('sessions')->pluck('id')->all())->toBe([$sessions[1]->id, $sessions[2]->id]);
    $this->get(route('personal.sesiones.index', ['until' => '2026-10-17']))->assertOk()->assertViewHas('sessions', fn ($rows) => $rows->total() === 3);
    $this->get(route('personal.sesiones.index', ['from' => '2026-10-18']))->assertOk()->assertViewHas('sessions', fn ($rows) => $rows->total() === 1);
    $this->get(route('personal.sesiones.index', ['room_id' => $sessions[1]->room_id]))->assertOk()->assertViewHas('sessions', fn ($rows) => $rows->total() === 1);
});
