<?php

namespace App\Http\Controllers\Api\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PerfilController extends Controller
{
    public function index(Request $request)
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
            
            // Obtener información completa del paciente
            $pacienteData = DB::table('pacientes as p')
                ->join('users as u', 'u.id', '=', 'p.user_id')
                ->where('p.id', $paciente->id)
                ->select([
                    'p.id',
                    'p.doc_tipo',
                    'p.doc_numero',
                    'p.birthdate',
                    'p.gender',
                    'u.name as nombre',
                    'u.email',
                    'u.phone',
                    'u.verified_at as email_verified_at',
                ])
                ->first();
            
            if (!$pacienteData) {
                return response()->json(['message' => 'No se pudo obtener la información del paciente'], 404);
            }
            
            // Calcular edad si hay fecha de nacimiento
            $edad = null;
            if ($pacienteData->birthdate) {
                try {
                    $edad = Carbon::parse($pacienteData->birthdate)->age;
                } catch (\Exception $e) {
                    Log::warning('Error al calcular edad del paciente', [
                        'birthdate' => $pacienteData->birthdate,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            // Estadísticas generales
            $stats = [
                'total_citas' => 0,
                'citas_pendientes' => 0,
                'citas_completadas' => 0,
                'citas_canceladas' => 0,
                'medicos_favoritos' => 0,
            ];
            
            try {
                $stats = [
                    'total_citas' => (int) DB::table('citas')->where('paciente_id', $paciente->id)->count(),
                    'citas_pendientes' => (int) DB::table('citas')
                        ->where('paciente_id', $paciente->id)
                        ->where('estado', 'pendiente')
                        ->where('starts_at', '>=', now())
                        ->count(),
                    'citas_completadas' => (int) DB::table('citas')
                        ->where('paciente_id', $paciente->id)
                        ->where('estado', 'completada')
                        ->count(),
                    'citas_canceladas' => (int) DB::table('citas')
                        ->where('paciente_id', $paciente->id)
                        ->where('estado', 'cancelada')
                        ->count(),
                    'medicos_favoritos' => (int) DB::table('paciente_favoritos')
                        ->where('paciente_id', $paciente->id)
                        ->count(),
                ];
            } catch (\Exception $e) {
                Log::warning('Error al obtener estadísticas', ['error' => $e->getMessage()]);
            }
            
            // Próximas citas (próximas 5)
            $proximasCitas = collect();
            try {
                $proximasCitas = DB::table('citas as c')
                    ->join('medicos as m', 'm.id', '=', 'c.medico_id')
                    ->join('users as u', 'u.id', '=', 'm.user_id')
                    ->leftJoin('especialidades as e', 'e.id', '=', 'c.especialidad_id')
                    ->where('c.paciente_id', $paciente->id)
                    ->where('c.starts_at', '>=', now())
                    ->where('c.estado', '!=', 'cancelada')
                    ->select([
                        'c.id',
                        'c.starts_at',
                        'c.ends_at',
                        'c.estado',
                        'c.notas as motivo',
                        'u.name as medico_nombre',
                        'e.nombre as especialidad_nombre',
                    ])
                    ->orderBy('c.starts_at', 'asc')
                    ->limit(5)
                    ->get()
                    ->map(function($cita) {
                        try {
                            return [
                                'id' => $cita->id,
                                'fecha' => Carbon::parse($cita->starts_at)->format('Y-m-d'),
                                'hora' => Carbon::parse($cita->starts_at)->format('H:i'),
                                'fecha_completa' => Carbon::parse($cita->starts_at)->format('d/m/Y H:i'),
                                'estado' => $cita->estado ?? 'pendiente',
                                'medico_nombre' => $cita->medico_nombre ?? 'Médico',
                                'especialidad_nombre' => $cita->especialidad_nombre ?? 'Sin especialidad',
                                'motivo' => $cita->motivo ?? null,
                            ];
                        } catch (\Exception $e) {
                            Log::warning('Error al procesar cita', ['cita_id' => $cita->id ?? null, 'error' => $e->getMessage()]);
                            return null;
                        }
                    })
                    ->filter();
            } catch (\Exception $e) {
                Log::warning('Error al obtener próximas citas', ['error' => $e->getMessage()]);
            }
            
            // Historial reciente (últimas 10 citas completadas o canceladas)
            $historial = collect();
            try {
                $historial = DB::table('citas as c')
                    ->join('medicos as m', 'm.id', '=', 'c.medico_id')
                    ->join('users as u', 'u.id', '=', 'm.user_id')
                    ->leftJoin('especialidades as e', 'e.id', '=', 'c.especialidad_id')
                    ->where('c.paciente_id', $paciente->id)
                    ->whereIn('c.estado', ['completada', 'cancelada'])
                    ->select([
                        'c.id',
                        'c.starts_at',
                        'c.estado',
                        'c.notas as motivo',
                        'u.name as medico_nombre',
                        'e.nombre as especialidad_nombre',
                    ])
                    ->orderBy('c.starts_at', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(function($cita) {
                        try {
                            return [
                                'id' => $cita->id,
                                'fecha' => Carbon::parse($cita->starts_at)->format('d/m/Y'),
                                'hora' => Carbon::parse($cita->starts_at)->format('H:i'),
                                'estado' => $cita->estado ?? 'completada',
                                'medico_nombre' => $cita->medico_nombre ?? 'Médico',
                                'especialidad_nombre' => $cita->especialidad_nombre ?? 'Sin especialidad',
                                'motivo' => $cita->motivo ?? null,
                            ];
                        } catch (\Exception $e) {
                            Log::warning('Error al procesar cita del historial', ['cita_id' => $cita->id ?? null, 'error' => $e->getMessage()]);
                            return null;
                        }
                    })
                    ->filter();
            } catch (\Exception $e) {
                Log::warning('Error al obtener historial', ['error' => $e->getMessage()]);
            }
            
            // Médicos favoritos
            $favoritos = collect();
            try {
                $favoritos = DB::table('paciente_favoritos as pf')
                    ->join('medicos as m', 'm.id', '=', 'pf.medico_id')
                    ->join('users as u', 'u.id', '=', 'm.user_id')
                    ->leftJoin('medico_especialidad as me', 'me.medico_id', '=', 'm.id')
                    ->leftJoin('especialidades as e', 'e.id', '=', 'me.especialidad_id')
                    ->where('pf.paciente_id', $paciente->id)
                    ->select([
                        'm.id',
                        'u.name as nombre',
                        'm.verif_status',
                        'e.nombre as especialidad_nombre',
                    ])
                    ->distinct()
                    ->get()
                    ->map(function($medico) {
                        return [
                            'id' => (int) $medico->id,
                            'nombre' => (string) ($medico->nombre ?? 'Médico'),
                            'verif_status' => $medico->verif_status ?? null,
                            'especialidad_nombre' => $medico->especialidad_nombre ?? 'Sin especialidad',
                        ];
                    });
            } catch (\Exception $e) {
                Log::warning('Error al obtener médicos favoritos', ['error' => $e->getMessage()]);
            }
            
            return response()->json([
                'paciente' => [
                    'id' => (int) $pacienteData->id,
                    'nombre' => (string) ($pacienteData->nombre ?? ''),
                    'email' => (string) ($pacienteData->email ?? ''),
                    'phone' => $pacienteData->phone ? (string) $pacienteData->phone : null,
                    'doc_tipo' => $pacienteData->doc_tipo ? (string) $pacienteData->doc_tipo : null,
                    'doc_numero' => $pacienteData->doc_numero ? (string) $pacienteData->doc_numero : null,
                    'birthdate' => $pacienteData->birthdate ? Carbon::parse($pacienteData->birthdate)->format('Y-m-d') : null,
                    'edad' => $edad,
                    'gender' => $pacienteData->gender ? (string) $pacienteData->gender : null,
                    'email_verified_at' => $pacienteData->email_verified_at,
                ],
                'stats' => $stats,
                'proximas_citas' => $proximasCitas->values()->all(),
                'historial' => $historial->values()->all(),
                'favoritos' => $favoritos->values()->all(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al obtener perfil del paciente', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => 'Error al cargar el perfil',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }
    
    public function update(Request $request)
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
            
            $data = $request->validate([
                'name' => 'sometimes|string|max:120',
                'phone' => 'sometimes|nullable|string|max:30',
                'doc_tipo' => 'sometimes|nullable|string|max:10',
                'doc_numero' => 'sometimes|nullable|string|max:32',
                'birthdate' => 'sometimes|nullable|date',
                'gender' => 'sometimes|nullable|string|max:32',
            ]);
            
            DB::transaction(function () use ($data, $user, $paciente) {
                // Actualizar datos del usuario
                if (isset($data['name'])) {
                    $user->name = $data['name'];
                }
                if (isset($data['phone'])) {
                    $user->phone = $data['phone'];
                }
                $user->save();
                
                // Actualizar datos del paciente
                if (isset($data['doc_tipo'])) {
                    $paciente->doc_tipo = $data['doc_tipo'];
                }
                if (isset($data['doc_numero'])) {
                    $paciente->doc_numero = $data['doc_numero'];
                }
                if (isset($data['birthdate'])) {
                    $paciente->birthdate = $data['birthdate'];
                }
                if (isset($data['gender'])) {
                    $paciente->gender = $data['gender'];
                }
                $paciente->save();
            });
            
            return response()->json(['message' => 'Perfil actualizado correctamente']);
        } catch (\Throwable $e) {
            Log::error('Error al actualizar perfil del paciente', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Error al actualizar el perfil',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }
}