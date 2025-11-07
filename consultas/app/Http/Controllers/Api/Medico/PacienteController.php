<?php

namespace App\Http\Controllers\Api\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PacienteController extends Controller
{
    /**
     * Obtener información completa de un paciente para el médico
     * GET /api/medico/pacientes/{id}
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Usuario no autenticado'], 401);
            }

            $medico = DB::table('medicos')->where('user_id', $user->id)->first();
            if (!$medico) {
                return response()->json(['message' => 'No es médico'], 403);
            }

            // Obtener información del paciente
            $paciente = DB::table('pacientes as p')
                ->join('users as u', 'u.id', '=', 'p.user_id')
                ->where('p.id', $id)
                ->select([
                    'p.id',
                    'p.doc_tipo',
                    'p.doc_numero',
                    'p.birthdate',
                    'p.gender',
                    'u.name as nombre',
                    'u.email',
                    'u.phone',
                ])
                ->first();

            if (!$paciente) {
                return response()->json(['message' => 'Paciente no encontrado'], 404);
            }

            // Calcular edad
            $edad = null;
            if ($paciente->birthdate) {
                try {
                    $edad = Carbon::parse($paciente->birthdate)->age;
                } catch (\Exception $e) {
                    Log::warning('Error al calcular edad', ['birthdate' => $paciente->birthdate]);
                }
            }

            // Estadísticas con este médico
            $stats = [
                'total_citas' => 0,
                'citas_completadas' => 0,
                'citas_pendientes' => 0,
                'citas_canceladas' => 0,
                'primera_cita' => null,
                'ultima_cita' => null,
            ];

            try {
                $citasConMedico = DB::table('citas')
                    ->where('medico_id', $medico->id)
                    ->where('paciente_id', $id)
                    ->select('estado', 'starts_at', 'created_at')
                    ->orderBy('starts_at', 'asc')
                    ->get();

                $stats['total_citas'] = $citasConMedico->count();
                $stats['citas_completadas'] = $citasConMedico->where('estado', 'completada')->count();
                $stats['citas_pendientes'] = $citasConMedico->where('estado', 'pendiente')->count();
                $stats['citas_canceladas'] = $citasConMedico->where('estado', 'cancelada')->count();

                if ($citasConMedico->count() > 0) {
                    $stats['primera_cita'] = Carbon::parse($citasConMedico->first()->starts_at)->format('Y-m-d');
                    $stats['ultima_cita'] = Carbon::parse($citasConMedico->last()->starts_at)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                Log::warning('Error al obtener estadísticas', ['error' => $e->getMessage()]);
            }

            // Historial de citas con este médico
            $historial = collect();
            try {
                $historial = DB::table('citas as c')
                    ->leftJoin('especialidades as e', 'e.id', '=', 'c.especialidad_id')
                    ->where('c.medico_id', $medico->id)
                    ->where('c.paciente_id', $id)
                    ->select([
                        'c.id',
                        'c.starts_at',
                        'c.ends_at',
                        'c.estado',
                        'c.motivo',
                        'c.notas',
                        'c.rating',
                        'c.review',
                        'e.nombre as especialidad_nombre',
                    ])
                    ->orderBy('c.starts_at', 'desc')
                    ->limit(20)
                    ->get()
                    ->map(function($cita) {
                        return [
                            'id' => (int) $cita->id,
                            'fecha' => Carbon::parse($cita->starts_at)->format('d/m/Y'),
                            'hora' => Carbon::parse($cita->starts_at)->format('H:i'),
                            'fecha_completa' => Carbon::parse($cita->starts_at)->format('d/m/Y H:i'),
                            'estado' => $cita->estado ?? 'pendiente',
                            'especialidad_nombre' => $cita->especialidad_nombre ?? 'Sin especialidad',
                            'motivo' => $cita->motivo ?? null,
                            'notas' => $cita->notas ?? null,
                            'rating' => $cita->rating ?? null,
                            'review' => $cita->review ?? null,
                        ];
                    });
            } catch (\Exception $e) {
                Log::warning('Error al obtener historial', ['error' => $e->getMessage()]);
            }

            // Citas próximas con este médico
            $proximasCitas = collect();
            try {
                $proximasCitas = DB::table('citas as c')
                    ->leftJoin('especialidades as e', 'e.id', '=', 'c.especialidad_id')
                    ->where('c.medico_id', $medico->id)
                    ->where('c.paciente_id', $id)
                    ->where('c.starts_at', '>=', now())
                    ->where('c.estado', '!=', 'cancelada')
                    ->select([
                        'c.id',
                        'c.starts_at',
                        'c.estado',
                        'c.motivo',
                        'e.nombre as especialidad_nombre',
                    ])
                    ->orderBy('c.starts_at', 'asc')
                    ->limit(5)
                    ->get()
                    ->map(function($cita) {
                        return [
                            'id' => (int) $cita->id,
                            'fecha' => Carbon::parse($cita->starts_at)->format('d/m/Y'),
                            'hora' => Carbon::parse($cita->starts_at)->format('H:i'),
                            'fecha_completa' => Carbon::parse($cita->starts_at)->format('d/m/Y H:i'),
                            'estado' => $cita->estado ?? 'pendiente',
                            'especialidad_nombre' => $cita->especialidad_nombre ?? 'Sin especialidad',
                            'motivo' => $cita->motivo ?? null,
                        ];
                    });
            } catch (\Exception $e) {
                Log::warning('Error al obtener próximas citas', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'paciente' => [
                    'id' => (int) $paciente->id,
                    'nombre' => (string) ($paciente->nombre ?? ''),
                    'email' => (string) ($paciente->email ?? ''),
                    'phone' => $paciente->phone ? (string) $paciente->phone : null,
                    'doc_tipo' => $paciente->doc_tipo ? (string) $paciente->doc_tipo : null,
                    'doc_numero' => $paciente->doc_numero ? (string) $paciente->doc_numero : null,
                    'birthdate' => $paciente->birthdate ? Carbon::parse($paciente->birthdate)->format('Y-m-d') : null,
                    'edad' => $edad,
                    'gender' => $paciente->gender ? (string) $paciente->gender : null,
                ],
                'stats' => $stats,
                'historial' => $historial->values()->all(),
                'proximas_citas' => $proximasCitas->values()->all(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al obtener información del paciente', [
                'user_id' => $request->user()?->id,
                'paciente_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al cargar la información del paciente',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }
}