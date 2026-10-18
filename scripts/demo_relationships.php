<?php

// Read-only demonstration after php artisan migrate --seed.
use App\Models\Experience;
use App\Models\GameSession;
use App\Models\Incident;
use App\Models\Reservation;
use App\Models\Team;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$experience = Experience::where('slug', 'expedicion-maldita')->firstOrFail();
$team = Team::with('users')->where('name', 'Los Escarabajos Demo')->firstOrFail();
$session = GameSession::with(['hints', 'preparationChecks', 'result'])->where('status', 'completed')->firstOrFail();
$incident = Incident::with('maintenanceActions.technician')->where('title', 'Sensor demo revisado')->firstOrFail();

echo json_encode([
    'experience' => $experience->name,
    'rates_cents' => $experience->rates()->orderBy('players')->pluck('total_cents', 'players'),
    'rooms_1_to_n' => $experience->rooms()->pluck('name'),
    'customer_reservations_1_to_n' => Reservation::firstOrFail()->user->reservations()->count(),
    'team_users_n_to_n' => $team->users->map(fn ($user) => [
        'email' => $user->email, 'role' => $user->pivot->role, 'joined_at' => $user->pivot->joined_at->toDateTimeString(),
    ]),
    'session_hints_n_to_n' => $session->hints->map(fn ($hint) => [
        'code' => $hint->code, 'used_at' => $hint->pivot->used_at->toDateTimeString(), 'penalty_seconds' => $hint->pivot->penalty_seconds,
    ]),
    'preparation_checks' => $session->preparationChecks->count(),
    'installation' => ['room' => $session->installation->room->name, 'experience' => $session->installation->experience->name],
    'template_items' => $session->preparationTemplate?->items->pluck('label'),
    'participants' => $session->result?->reservation?->participants->pluck('display_name'),
    'payments_cents' => $session->result?->reservation?->payments->map(fn ($payment) => ['kind' => $payment->kind, 'amount_cents' => $payment->amount_cents]),
    'translations' => $experience->translations->pluck('title', 'locale'),
    'result_1_to_1' => ['escaped' => $session->result->escaped, 'elapsed_seconds' => $session->result->elapsed_seconds],
    'maintenance_actions_1_to_n' => $incident->maintenanceActions->map(fn ($action) => ['description' => $action->description, 'technician' => $action->technician->email]),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
