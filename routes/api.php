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
});
