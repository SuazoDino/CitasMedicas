<?php

namespace App\Http\Controllers\Api\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Services\PacienteDashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private PacienteDashboardService $dashboardService)
    {
    }

    public function resumen(Request $request)
    {
        $user = $request->user();
        $paciente = Paciente::where('user_id', $user->id)->first();

        if (!$paciente) {
            return response()->json(['message' => 'El usuario no está registrado como paciente'], 403);
        }

        return response()->json($this->dashboardService->buildResumen($paciente));
    }
}