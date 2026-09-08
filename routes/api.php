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

// Health-check
Route::get('/_ping', fn () => response()->json(['ok' => true, 'ts' => now()]));
