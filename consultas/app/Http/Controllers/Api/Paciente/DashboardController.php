<?php

namespace App\Http\Controllers\Api\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Services\PacienteDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct(private PacienteDashboardService $dashboardService)
    {
    }

    public function resumen(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['message' => 'Usuario no autenticado'], 401);
            }
            
            $paciente = Paciente::where('user_id', $user->id)->first();

            if (!$paciente) {
                return response()->json(['message' => 'El usuario no está registrado como paciente'], 403);
            }

            $resumen = $this->dashboardService->buildResumen($paciente);
            
            // Asegurar que siempre retornamos un array válido
            if (!is_array($resumen)) {
                Log::warning('buildResumen no retornó un array válido', ['resumen' => $resumen]);
                $resumen = [
                    'stats' => ['proximas' => 0, 'completadas' => 0, 'historial' => 0, 'favoritos' => 0],
                    'historial' => [],
                    'recomendados' => [],
                    'tips' => ['Bienvenido a MediReserva. Comienza agendando tu primera cita.'],
                ];
            }
            
            return response()->json($resumen);
        } catch (\Throwable $e) {
            Log::error('Error en resumen de paciente: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error al cargar el resumen',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}