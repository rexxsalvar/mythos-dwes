<?php

use App\Models\GameSession;
use App\Models\Incident;
use App\Models\Reservation;
use App\Models\User;
use Database\Seeders\AccessRolesSeeder;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(AccessRolesSeeder::class);
    $this->withoutVite();
});

test('private areas redirect guests and return 401 for JSON requests', function () {
    foreach (['dashboard', 'profile.edit', 'cliente.reservas.index', 'personal.reservas.index', 'personal.sesiones.index', 'mantenimiento.incidencias.index', 'administration.index'] as $route) {
        $this->get(route($route))->assertRedirect(route('login'));
        $this->getJson(route($route))->assertUnauthorized();
    }
});

test('five roles have different real access to private areas', function (string $role, array $allowed) {
    $user = User::factory()->create()->assignRole($role);
    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    $this->get(route('profile.edit'))->assertOk();
    foreach (['cliente.reservas.index', 'personal.reservas.index', 'personal.sesiones.index', 'mantenimiento.incidencias.index', 'administration.index'] as $route) {
        $this->get(route($route))->assertStatus(in_array($route, $allowed, true) ? 200 : 403);
    }
})->with([
    'cliente' => ['cliente', ['cliente.reservas.index']],
    'recepcion' => ['recepcion', ['personal.reservas.index', 'personal.sesiones.index']],
    'game master' => ['game_master', ['personal.sesiones.index']],
    'mantenimiento' => ['mantenimiento', ['mantenimiento.incidencias.index']],
    'administrador' => ['administrador', ['cliente.reservas.index', 'personal.reservas.index', 'personal.sesiones.index', 'mantenimiento.incidencias.index', 'administration.index']],
]);

test('users without roles have no private area access', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
    $this->get(route('profile.edit'))->assertForbidden();
    $this->get(route('cliente.reservas.index'))->assertForbidden();
});

test('reservation policy allows only ownership or explicit staff permission', function () {
    $owner = User::factory()->create()->assignRole('cliente');
    $foreign = Reservation::factory()->create();
    $own = Reservation::factory()->for($owner)->create();
    $this->actingAs($owner)->get(route('cliente.reservas.index'))->assertOk()->assertSee($own->reference)->assertDontSee($foreign->reference);
    $this->get(route('cliente.reservas.show', $own))->assertOk()->assertSee('Importe del grupo');
    $this->get(route('cliente.reservas.show', $foreign))->assertForbidden()->assertDontSee($foreign->reference);
    foreach (['recepcion', 'administrador'] as $role) {
        $staff = User::factory()->create()->assignRole($role);
        $this->actingAs($staff)->get(route('personal.reservas.show', $foreign))->assertOk()->assertSee($foreign->reference);
    }
    expect(Gate::forUser($owner)->allows('viewAny', Reservation::class))->toBeTrue();
    $owner->is_active = false;
    expect(Gate::forUser($owner)->allows('view', $own))->toBeFalse();
});

test('session policy and agenda restrict game masters to assigned sessions', function () {
    $master = User::factory()->create()->assignRole('game_master');
    $own = GameSession::factory()->create(['game_master_id' => $master->id]);
    $foreign = GameSession::factory()->create();
    $this->actingAs($master)->get(route('personal.sesiones.index'))->assertOk()
        ->assertViewHas('sessions', fn ($rows) => $rows->pluck('id')->all() === [$own->id]);
    $this->get(route('personal.sesiones.show', $own))->assertOk();
    $this->get(route('personal.sesiones.show', $foreign))->assertForbidden();
    foreach (['recepcion', 'administrador'] as $role) {
        $staff = User::factory()->create()->assignRole($role);
        $this->actingAs($staff)->get(route('personal.sesiones.show', $foreign))->assertOk();
    }
    expect(Gate::forUser($master)->allows('viewAny', GameSession::class))->toBeTrue();
    $master->is_active = false;
    expect(Gate::forUser($master)->allows('view', $own))->toBeFalse();
});

test('maintenance queries hide other technicians incidents even through direct URLs', function () {
    $technician = User::factory()->create()->assignRole('mantenimiento');
    $own = Incident::factory()->create(['assigned_to' => $technician->id, 'title' => 'Incidencia propia']);
    $foreign = Incident::factory()->create(['title' => 'Incidencia ajena confidencial']);
    $this->actingAs($technician)->get(route('mantenimiento.incidencias.index'))->assertOk()->assertSee($own->title)->assertDontSee($foreign->title);
    $this->get(route('mantenimiento.incidencias.show', $own))->assertOk();
    $this->get(route('mantenimiento.incidencias.show', $foreign))->assertNotFound()->assertDontSee($foreign->title);
    $admin = User::factory()->create()->assignRole('administrador');
    $this->actingAs($admin)->get(route('mantenimiento.incidencias.show', $foreign))->assertOk();
});

test('access seeding is idempotent and preserves users and their assignments', function () {
    $client = User::factory()->create()->assignRole('cliente');
    $this->seed(AccessRolesSeeder::class);
    expect(Role::count())->toBe(5)->and(Permission::count())->toBe(8)
        ->and(User::count())->toBe(1)->and($client->fresh()->getRoleNames()->all())->toBe(['cliente'])
        ->and($client->fresh()->can('administration.view'))->toBeFalse();
});
