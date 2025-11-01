<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; // 👈 OJO: Api, no Auth
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\PacienteCitasController;
use App\Http\Controllers\Api\MedicoCitasController;
use App\Http\Controllers\Api\MedicoSlotsController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\HorariosController;
// Público
Route::prefix('auth')->group(function () {
    Route::post('register/paciente', [AuthController::class, 'registerPaciente']);
    Route::post('register/medico',   [AuthController::class, 'registerMedico']);
    Route::post('login',             [AuthController::class, 'login']);
    Route::post('forgot-password',   [PasswordResetController::class, 'sendLink']);
    Route::post('reset-password',    [PasswordResetController::class, 'reset']);
});

// Protegido
Route::prefix('public')->group(function () {
    Route::get('especialidades', [CatalogoController::class, 'especialidades']);
    Route::get('medicos',        [CatalogoController::class, 'medicos']);
    Route::get('medicos/{medico}/slots', [MedicoSlotsController::class, 'slots']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('me',      [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    Route::prefix('paciente')->group(function () {
        Route::get('citas/proximas', [PacienteCitasController::class, 'proximas']);
        Route::post('citas',         [PacienteCitasController::class, 'store']);
        Route::post('citas/{id}/cancelar', [PacienteCitasController::class, 'cancelar']);
    });

    Route::prefix('medico')->group(function () {
        Route::get('citas',                [MedicoCitasController::class, 'delDia']);
        Route::post('citas/{id}/confirmar', [MedicoCitasController::class, 'confirmar']);
        Route::post('citas/{id}/cancelar',  [MedicoCitasController::class, 'cancelar']);
        Route::post('citas/{id}/completar', [MedicoCitasController::class, 'completar']);
        Route::get('horarios',             [MedicoHorariosController::class, 'index']);
        Route::post('horarios',            [MedicoHorariosController::class, 'store']);
        Route::put('horarios/{horario}',   [MedicoHorariosController::class, 'update']);
        Route::patch('horarios/{horario}', [MedicoHorariosController::class, 'update']);
        Route::delete('horarios/{horario}', [MedicoHorariosController::class, 'destroy']);
    });
});

Route::get('/_ping', fn () => response()->json(['ok' => true, 'ts' => now()]));
