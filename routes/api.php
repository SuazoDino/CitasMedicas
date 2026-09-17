<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — CitasMedicas
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas de la API.
| Todas las rutas tienen el prefijo /api automáticamente.
|
*/

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\CitaController;
use App\Http\Controllers\Paciente\MedicoController as PacienteMedicoController;
use App\Http\Controllers\Paciente\CitaController as PacienteCitaController;

// Health-check
Route::get('/_ping', fn () => response()->json(['ok' => true, 'ts' => now()]));

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Email verification route
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->name('verification.verify');

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Admin routes
    Route::get('/admin/dashboard', [AdminController::class, 'dashboardMetrics']);
    Route::apiResource('/admin/usuarios', UsuarioController::class);

    Route::get('/admin/citas-opciones', [CitaController::class, 'lookups']);
    Route::apiResource('/admin/citas', CitaController::class);

    // Rutas del paciente (búsqueda y reserva de citas)
    Route::get('/paciente/especialidades', [PacienteMedicoController::class, 'especialidades']);
    Route::get('/paciente/medicos', [PacienteMedicoController::class, 'index']);
    Route::get('/paciente/medicos/{id}', [PacienteMedicoController::class, 'show']);
    Route::get('/paciente/medicos/{id}/disponibilidad', [PacienteMedicoController::class, 'disponibilidad']);

    Route::get('/paciente/citas', [PacienteCitaController::class, 'index']);
    Route::post('/paciente/citas', [PacienteCitaController::class, 'store']);
    Route::patch('/paciente/citas/{id}/cancelar', [PacienteCitaController::class, 'cancelar']);
});
