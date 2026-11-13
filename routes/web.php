<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\GameSessionController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\OperationalCommandController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::get('/experiencias', [ExperienceController::class, 'index'])->name('experiences.index');
Route::get('/experiencias/{experience:slug}', [ExperienceController::class, 'show'])->name('experiences.show');

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
        Route::post('/reservas', [ReservationController::class, 'store'])->name('reservas.store');
        Route::get('/reservas/{reservation}', [ReservationController::class, 'show'])->whereNumber('reservation')->name('reservas.show');
        Route::delete('/reservas/{reservation}', [ReservationController::class, 'cancel'])->whereNumber('reservation')->name('reservas.cancel');
    });

    Route::prefix('personal')->name('personal.')->group(function () {
        Route::middleware('permission:reservations.view-all')->group(function () {
            Route::post('/reservas', [ReservationController::class, 'store'])->name('reservas.store');
            Route::get('/reservas', [ReservationController::class, 'index'])->name('reservas.index');
            Route::get('/reservas/{reservation}', [ReservationController::class, 'show'])->whereNumber('reservation')->name('reservas.show');
            Route::patch('/reservas/{reservation}/confirmar', [ReservationController::class, 'confirm'])->whereNumber('reservation')->name('reservas.confirm');
            Route::patch('/reservas/{reservation}', [ReservationController::class, 'update'])->whereNumber('reservation')->name('reservas.update');
            Route::delete('/reservas/{reservation}', [ReservationController::class, 'cancel'])->whereNumber('reservation')->name('reservas.cancel');
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
    Route::prefix('administracion')->name('administration.')->middleware('can:administration.view')->group(function () {
        Route::post('/operaciones/caducar-pendientes', [OperationalCommandController::class, 'expire'])->name('operations.expire');
        Route::post('/operaciones/revisar-reservas', [OperationalCommandController::class, 'review'])->name('operations.review');
        Route::post('/operaciones/actividad-mensual', [OperationalCommandController::class, 'monthly'])->name('operations.monthly');
        Route::get('/experiencias', [ExperienceController::class, 'manage'])->name('experiences.index');
        Route::get('/experiencias/{experience}/editar', [ExperienceController::class, 'edit'])->name('experiences.edit');
        Route::post('/experiencias', [ExperienceController::class, 'store'])->name('experiences.store');
        Route::patch('/experiencias/{experience}', [ExperienceController::class, 'update'])->name('experiences.update');
        Route::delete('/experiencias/{experience}', [ExperienceController::class, 'destroy'])->name('experiences.destroy');
        Route::get('/salas', [RoomController::class, 'index'])->name('rooms.index');
        Route::get('/salas/{room}/editar', [RoomController::class, 'edit'])->name('rooms.edit');
        Route::post('/salas', [RoomController::class, 'store'])->name('rooms.store');
        Route::patch('/salas/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/salas/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
        Route::get('/sesiones/crear', [GameSessionController::class, 'create'])->name('sessions.create');
        Route::get('/sesiones/{session}/editar', [GameSessionController::class, 'edit'])->name('sessions.edit');
        Route::post('/sesiones', [GameSessionController::class, 'store'])->name('sessions.store');
        Route::patch('/sesiones/{session}', [GameSessionController::class, 'update'])->name('sessions.update');
        Route::delete('/sesiones/{session}', [GameSessionController::class, 'destroy'])->name('sessions.destroy');
    });
});
