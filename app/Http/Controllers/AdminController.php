<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\ProfesionalMedico;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\LogAuditoria;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * Retorna las métricas y datos recientes para el Dashboard del Admin.
     */
    public function dashboardMetrics(): JsonResponse
    {
        $today = Carbon::today();

        // ── Estadísticas rápidas ──
        $totalPacientes = Paciente::count();

        $citasHoy = Cita::whereDate('fecha_hora_inicio', $today)->count();

        $medicosActivos = ProfesionalMedico::where('estado_validacion', 'aprobado')->count();

        $totalIngresos = Pago::where('estado_pago', 'pagado')->sum('monto');

        // ── Últimas 5 citas ──
        $recentAppointments = Cita::with([
                'paciente:id,nombres,apellidos',
                'profesional:id,nombres,apellidos',
            ])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($cita) {
                return [
                    'id' => $cita->id,
                    'paciente_nombre' => $cita->paciente
                        ? "{$cita->paciente->nombres} {$cita->paciente->apellidos}"
                        : 'N/A',
                    'profesional_nombre' => $cita->profesional
                        ? "Dr. {$cita->profesional->nombres} {$cita->profesional->apellidos}"
                        : 'N/A',
                    'fecha_hora' => $cita->fecha_hora_inicio
                        ? $cita->fecha_hora_inicio->format('d M, h:i A')
                        : 'N/A',
                    'estado' => $cita->estado,
                ];
            });

        // ── Actividad reciente (logs) ──
        $recentActivity = LogAuditoria::with('admin:id,nombres,apellidos')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'accion' => $log->accion,
                    'tabla_afectada' => $log->tabla_afectada,
                    'admin_nombre' => $log->admin
                        ? "{$log->admin->nombres} {$log->admin->apellidos}"
                        : 'Sistema',
                    'fecha' => $log->created_at
                        ? $log->created_at->diffForHumans()
                        : '',
                    'detalles' => $log->detalles,
                ];
            });

        return response()->json([
            'stats' => [
                'total_pacientes' => $totalPacientes,
                'citas_hoy' => $citasHoy,
                'medicos_activos' => $medicosActivos,
                'total_ingresos' => number_format($totalIngresos, 2),
            ],
            'recent_appointments' => $recentAppointments,
            'recent_activity' => $recentActivity,
        ]);
    }
}
