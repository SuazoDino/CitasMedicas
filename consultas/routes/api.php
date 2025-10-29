<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; // 👈 OJO: Api, no Auth

// 🔎 Diagnóstico: ping simple para confirmar que api.php se carga
Route::get('/_ping', fn() => response()->json(['ok' => true, 'ts' => now()]));

// Público
Route::prefix('auth')->group(function () {
    Route::post('register/paciente', [AuthController::class, 'registerPaciente']);
    Route::post('register/medico',   [AuthController::class, 'registerMedico']);
    Route::post('login',             [AuthController::class, 'login']);
});

// Protegido
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('me',     [AuthController::class, 'me']);
        Route::post('logout',[AuthController::class, 'logout']);
    });
});
