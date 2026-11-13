<?php

namespace App\Http\Controllers;

use App\Http\Requests\MonthlyActivityRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class OperationalCommandController extends Controller
{
    public function expire(): RedirectResponse
    {
        Gate::authorize('administration.view');
        $output = $this->run('reservations:expire');

        return redirect()->route('administration.index')->with('status', trim($output));
    }

    public function review(): RedirectResponse
    {
        Gate::authorize('administration.view');
        $output = $this->run('incidents:review-reservations');

        return redirect()->route('administration.index')->with('status', trim($output));
    }

    public function monthly(MonthlyActivityRequest $request): Response
    {
        $data = $request->validated();
        $arguments = ['month' => $data['month']];
        if (! empty($data['room_id'])) {
            $arguments['--room'] = (int) $data['room_id'];
        }
        $output = $this->run('reports:monthly', $arguments);

        return response($output, 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="actividad-mensual-'.$data['month'].'.json"',
        ]);
    }

    private function run(string $command, array $arguments = []): string
    {
        if (Artisan::call($command, $arguments) !== 0) {
            throw ValidationException::withMessages(['operations' => 'No se pudo completar la operación. Vuelve a intentarlo.']);
        }

        return Artisan::output();
    }
}
