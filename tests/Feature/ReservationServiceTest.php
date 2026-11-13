<?php

use App\Models\BookingPolicyVersion;
use App\Models\GameSession;
use App\Models\Rate;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessRolesSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(AccessRolesSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-11-13 10:00:00', 'UTC'));
    $this->customer = User::factory()->create()->assignRole('cliente');
    $this->staff = User::factory()->create()->assignRole('recepcion');
    $this->session = GameSession::factory()->create([
        'starts_at' => now()->addHours(2), 'ends_at' => now()->addHours(3), 'preparation_minutes' => 30,
    ]);
    $this->rate = Rate::factory()->create(['experience_id' => $this->session->experience_id]);
    $this->service = app(ReservationService::class);
});

test('holding computes price on the server and freezes the group snapshot across rate edits', function () {
    $reservation = $this->service->hold($this->customer, $this->session, 2);
    expect($reservation->expires_at->equalTo(now()->addMinutes(15)))->toBeTrue();
    $price = $reservation->total_cents;
    $this->rate->update(['total_cents' => 99000]);
    $confirmed = $this->service->confirm($this->staff, $reservation);
    expect($confirmed->status)->toBe('confirmed')
        ->and($confirmed->total_cents)->toBe($price)
        ->and($confirmed->players)->toBe(2)
        ->and($confirmed->events()->count())->toBe(2);
    expect(fn () => $this->service->hold($this->customer, $this->session, 2))->toThrow(ValidationException::class);
});

test('expiry is checked inside the reservation flow and committed when confirmation is refused', function () {
    $reservation = $this->service->hold($this->customer, $this->session, 2);
    $this->travel(15)->minutes();
    expect(fn () => $this->service->confirm($this->staff, $reservation))->toThrow(ValidationException::class);
    expect($reservation->fresh()->status)->toBe('expired');
    $next = $this->service->hold($this->customer, $this->session, 2);
    expect($next->id)->not->toBe($reservation->id);
});

test('a new hold expires the previous deadline atomically without requiring a scheduled command', function () {
    $previous = $this->service->hold($this->customer, $this->session, 2);
    $this->travel(15)->minutes();
    $nextCustomer = User::factory()->create()->assignRole('cliente');
    $next = $this->service->hold($nextCustomer, $this->session, 2);

    expect($previous->fresh()->status)->toBe('expired')
        ->and($previous->events()->where('action', 'expired')->count())->toBe(1)
        ->and($next->user_id)->toBe($nextCustomer->id)
        ->and($this->session->reservations()->activeAt(now())->count())->toBe(1);
});

test('the hold is shortened by preparation and capacity and authorization are enforced', function () {
    $this->session->update(['starts_at' => now()->addMinutes(40), 'ends_at' => now()->addMinutes(100)]);
    expect(fn () => $this->service->hold($this->customer, $this->session, 99))->toThrow(ValidationException::class);
    $reservation = $this->service->hold($this->customer, $this->session, 2);
    expect($reservation->expires_at->equalTo(now()->addMinutes(10)))->toBeTrue();
    $this->actingAs(User::factory()->create()->assignRole('cliente'))
        ->delete(route('cliente.reservas.cancel', $reservation))->assertForbidden();
    $this->actingAs($this->customer)->patch(route('personal.reservas.confirm', $reservation))->assertForbidden();
});

test('cancellation preserves history and requires the configured notice for confirmed reservations', function () {
    $this->session->update(['starts_at' => now()->addDays(2), 'ends_at' => now()->addDays(2)->addHour()]);
    $reservation = $this->service->hold($this->customer, $this->session, 2);
    $this->service->confirm($this->staff, $reservation);
    expect($reservation->bookingPolicyVersion->cancellation_notice_minutes)->toBe(1440);
    BookingPolicyVersion::factory()->create([
        'experience_id' => $this->session->experience_id, 'version' => 2, 'status' => 'active',
        'cancellation_notice_minutes' => 2880, 'valid_from' => now(),
    ]);
    $this->travel(24)->hours();
    $cancelled = $this->service->cancel($this->customer, $reservation);
    expect($cancelled->status)->toBe('cancelled')->and($cancelled->events()->count())->toBe(3);
    expect($this->session->reservations()->count())->toBe(1);
});

test('confirmed cancellation refuses a passed deadline and never invents conditions for legacy bookings', function () {
    $reservation = $this->service->hold($this->customer, $this->session, 2);
    $this->service->confirm($this->staff, $reservation);
    expect(fn () => $this->service->cancel($this->customer, $reservation))->toThrow(ValidationException::class);
    $reservation->update(['booking_policy_version_id' => null]);
    $this->session->update(['starts_at' => now()->addDays(2), 'ends_at' => now()->addDays(2)->addHour()]);
    expect(fn () => $this->service->cancel($this->customer, $reservation))->toThrow(ValidationException::class);
    expect($reservation->fresh()->status)->toBe('confirmed');
});

test('web writes ignore injected status price and customer identity', function () {
    $this->actingAs($this->customer)->post(route('cliente.reservas.store'), [
        'game_session_id' => $this->session->id, 'players' => 2,
        'status' => 'confirmed', 'total_cents' => 1, 'user_id' => $this->staff->id,
    ])->assertRedirect();
    $reservation = Reservation::sole();
    expect($reservation->status)->toBe('pending')->and($reservation->user_id)->toBe($this->customer->id)
        ->and($reservation->total_cents)->toBe($this->rate->total_cents);
});
