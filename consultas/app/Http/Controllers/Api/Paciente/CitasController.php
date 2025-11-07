<?php

namespace App\Http\Controllers\Api\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CitasController extends Controller
{
    private function pacienteId($userId)
    {
        return DB::table('pacientes')->where('user_id', $userId)->value('id');
    }

    /**
     * Obtener todas las citas del paciente con filtros
     * GET /api/paciente/citas?estado=pendiente&desde=2025-01-01&hasta=2025-12-31&medico_id=1&especialidad_id=1&q=busqueda
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Usuario no autenticado'], 401);
            }

            $pacienteId = $this->pacienteId($user->id);
            if (!$pacienteId) {
                return response()->json(['message' => 'No es paciente'], 403);
            }

            $query = DB::table('citas as c')
                ->join('medicos as m', 'm.id', '=', 'c.medico_id')
                ->join('users as u', 'u.id', '=', 'm.user_id')
                ->leftJoin('especialidades as e', 'e.id', '=', 'c.especialidad_id')
                ->where('c.paciente_id', $pacienteId)
                ->select([
                    'c.id',
                    'c.medico_id',
                    'c.especialidad_id',
                    'c.starts_at',
                    'c.ends_at',
                    'c.estado',
                    'c.motivo',
                    'c.notas',
                    'c.created_at',
                    'c.updated_at',
                    'u.name as medico_nombre',
                    'e.nombre as especialidad_nombre',
                    'm.verif_status',
                ]);

            // Filtros
            if ($request->has('estado') && $request->estado) {
                $query->where('c.estado', $request->estado);
            }

            if ($request->has('desde') && $request->desde) {
                $query->whereDate('c.starts_at', '>=', $request->desde);
            }

            if ($request->has('hasta') && $request->hasta) {
                $query->whereDate('c.starts_at', '<=', $request->hasta);
            }

            if ($request->has('medico_id') && $request->medico_id) {
                $query->where('c.medico_id', $request->medico_id);
            }

            if ($request->has('especialidad_id') && $request->especialidad_id) {
                $query->where('c.especialidad_id', $request->especialidad_id);
            }

            // Búsqueda por texto (nombre de médico, especialidad, motivo)
            if ($request->has('q') && $request->q) {
                $q = trim($request->q);
                $q = str_replace(['%', '_'], ['\%', '\_'], $q);
                $query->where(function($qry) use ($q) {
                    $qry->where('u.name', 'like', "%{$q}%")
                        ->orWhere('e.nombre', 'like', "%{$q}%")
                        ->orWhere('c.motivo', 'like', "%{$q}%")
                        ->orWhere('c.notas', 'like', "%{$q}%");
                });
            }

            // Ordenamiento
            $orderBy = $request->get('order_by', 'starts_at');
            $orderDir = $request->get('order_dir', 'desc');
            $query->orderBy("c.{$orderBy}", $orderDir);

            // Paginación
            $perPage = min((int) $request->get('per_page', 20), 100);
            $citas = $query->paginate($perPage);

            $citas->getCollection()->transform(function($cita) {
                $startsAt = Carbon::parse($cita->starts_at);
                $estado = $cita->estado ?? 'pendiente';
                return [
                    'id' => (int) $cita->id,
                    'medico_id' => (int) $cita->medico_id,
                    'medico_nombre' => $cita->medico_nombre ?? 'Médico',
                    'medico_verificado' => $cita->verif_status === 'verificado',
                    'especialidad_id' => (int) $cita->especialidad_id,
                    'especialidad_nombre' => $cita->especialidad_nombre ?? 'Sin especialidad',
                    'fecha' => $startsAt->format('Y-m-d'),
                    'hora' => $startsAt->format('H:i'),
                    'fecha_completa' => $startsAt->format('d/m/Y H:i'),
                    'fecha_humana' => $startsAt->locale('es')->diffForHumans(),
                    'starts_at' => $startsAt->toIso8601String(),
                    'ends_at' => Carbon::parse($cita->ends_at)->toIso8601String(),
                    'estado' => $estado,
                    'motivo' => $cita->motivo ?? null,
                    'notas' => $cita->notas ?? null,
                    'created_at' => $cita->created_at,
                    'updated_at' => $cita->updated_at,
                    'es_pasada' => $startsAt->isPast(),
                    'es_hoy' => $startsAt->isToday(),
                    'es_proxima' => $startsAt->isFuture(),
                    'puede_cancelar' => !in_array($estado, ['cancelada', 'completada']),
                    'puede_reprogramar' => !in_array($estado, ['cancelada', 'completada']) && $startsAt->isFuture(),
                    'rating' => $cita->rating ?? null,
                    'review' => $cita->review ?? null,
                ];
            });

            return response()->json([
                'data' => $citas->items(),
                'pagination' => [
                    'current_page' => $citas->currentPage(),
                    'last_page' => $citas->lastPage(),
                    'per_page' => $citas->perPage(),
                    'total' => $citas->total(),
                    'from' => $citas->firstItem(),
                    'to' => $citas->lastItem(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al obtener citas del paciente', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al cargar las citas',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Obtener una cita específica con todos sus detalles
     * GET /api/paciente/citas/{id}
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Usuario no autenticado'], 401);
            }

            $pacienteId = $this->pacienteId($user->id);
            if (!$pacienteId) {
                return response()->json(['message' => 'No es paciente'], 403);
            }

            $cita = DB::table('citas as c')
                ->join('medicos as m', 'm.id', '=', 'c.medico_id')
                ->join('users as u', 'u.id', '=', 'm.user_id')
                ->leftJoin('especialidades as e', 'e.id', '=', 'c.especialidad_id')
                ->where('c.id', $id)
                ->where('c.paciente_id', $pacienteId)
                ->select([
                    'c.*',
                    'u.name as medico_nombre',
                    'u.email as medico_email',
                    'u.phone as medico_phone',
                    'e.nombre as especialidad_nombre',
                    'm.verif_status',
                ])
                ->first();
            
            if (!$cita) {
                return response()->json(['message' => 'Cita no encontrada'], 404);
            }
            
            $startsAt = Carbon::parse($cita->starts_at);
            $estado = $cita->estado ?? 'pendiente';

            return response()->json([
                'id' => (int) $cita->id,
                'medico_id' => (int) $cita->medico_id,
                'medico_nombre' => $cita->medico_nombre ?? 'Médico',
                'medico_email' => $cita->medico_email ?? null,
                'medico_phone' => $cita->medico_phone ?? null,
                'medico_verificado' => $cita->verif_status === 'verificado',
                'especialidad_id' => (int) $cita->especialidad_id,
                'especialidad_nombre' => $cita->especialidad_nombre ?? 'Sin especialidad',
                'fecha' => Carbon::parse($cita->starts_at)->format('Y-m-d'),
                'hora' => Carbon::parse($cita->starts_at)->format('H:i'),
                'fecha_completa' => Carbon::parse($cita->starts_at)->format('d/m/Y H:i'),
                'fecha_humana' => Carbon::parse($cita->starts_at)->locale('es')->diffForHumans(),
                'starts_at' => Carbon::parse($cita->starts_at)->toIso8601String(),
                'ends_at' => Carbon::parse($cita->ends_at)->toIso8601String(),
                'estado' => $cita->estado ?? 'pendiente',
                'motivo' => $cita->motivo ?? null,
                'notas' => $cita->notas ?? null,
                'cancel_reason' => $cita->cancel_reason ?? null,
                'created_at' => $cita->created_at,
                'updated_at' => $cita->updated_at,
                'reminder_sent_at' => $cita->reminder_sent_at ?? null,
                'es_pasada' => Carbon::parse($cita->starts_at)->isPast(),
                'es_hoy' => Carbon::parse($cita->starts_at)->isToday(),
                'es_proxima' => Carbon::parse($cita->starts_at)->isFuture(),
                'puede_cancelar' => !in_array($estado, ['cancelada', 'completada']),
                'puede_reprogramar' => !in_array($estado, ['cancelada', 'completada']) && $startsAt->isFuture(),
                'rating' => $cita->rating ?? null,
                'review' => $cita->review ?? null,
                'rated_at' => $cita->rated_at ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al obtener detalle de cita', [
                'user_id' => $request->user()?->id,
                'cita_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al cargar la cita',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }
}
