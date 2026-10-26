<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameSessionController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/acceso', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/acceso', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/registro', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/registro', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1')->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/salir', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/panel', [DashboardController::class, 'index'])->middleware('can:profile.update-own')->name('dashboard');
    Route::get('/mi-cuenta', [ProfileController::class, 'edit'])->middleware('can:profile.update-own')->name('profile.edit');
    Route::patch('/mi-cuenta', [ProfileController::class, 'update'])->middleware('can:profile.update-own')->name('profile.update');

    Route::prefix('cliente')->name('cliente.')->middleware('permission:reservations.view-own')->group(function () {
        Route::get('/reservas', [ReservationController::class, 'own'])->name('reservas.index');
        Route::get('/reservas/{reservation}', [ReservationController::class, 'show'])->whereNumber('reservation')->name('reservas.show');
    });

    Route::prefix('personal')->name('personal.')->group(function () {
        Route::middleware('permission:reservations.view-all')->group(function () {
            Route::get('/reservas', [ReservationController::class, 'index'])->name('reservas.index');
            Route::get('/reservas/{reservation}', [ReservationController::class, 'show'])->whereNumber('reservation')->name('reservas.show');
        });
        Route::middleware('permission:sessions.view-assigned|sessions.view-all')->group(function () {
            Route::get('/sesiones', [GameSessionController::class, 'index'])->name('sesiones.index');
            Route::get('/sesiones/{session}', [GameSessionController::class, 'show'])->whereNumber('session')->name('sesiones.show');
        });
    });

    Route::prefix('mantenimiento')->name('mantenimiento.')->middleware('permission:incidents.view-assigned|incidents.view-all')->group(function () {
        Route::get('/incidencias', [IncidentController::class, 'index'])->name('incidencias.index');
        Route::get('/incidencias/{incident}', [IncidentController::class, 'show'])->whereNumber('incident')->name('incidencias.show');
    });

    Route::get('/administracion', [DashboardController::class, 'administration'])->middleware('can:administration.view')->name('administration.index');
});
