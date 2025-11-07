<?php

namespace App\Http\Controllers\Api\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RatingController extends Controller
{
    private function pacienteId($userId)
    {
        return DB::table('pacientes')->where('user_id', $userId)->value('id');
    }

    /**
     * Calificar una cita completada
     * POST /api/paciente/citas/{id}/rating
     */
    public function store(Request $request, $id)
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

            $data = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string|max:500',
            ]);

            $cita = DB::table('citas')
                ->where('id', $id)
                ->where('paciente_id', $pacienteId)
                ->first();

            if (!$cita) {
                return response()->json(['message' => 'Cita no encontrada'], 404);
            }

            if ($cita->estado !== 'completada') {
                return response()->json([
                    'message' => 'Solo puedes calificar citas completadas'
                ], 422);
            }

            DB::table('citas')
                ->where('id', $id)
                ->update([
                    'rating' => $data['rating'],
                    'review' => $data['review'] ?? null,
                    'rated_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

            return response()->json([
                'message' => 'Calificación guardada correctamente',
                'rating' => $data['rating'],
                'review' => $data['review'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al calificar cita', [
                'user_id' => $request->user()?->id,
                'cita_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al guardar la calificación',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Actualizar una calificación existente
     * PUT /api/paciente/citas/{id}/rating
     */
    public function update(Request $request, $id)
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

            $data = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string|max:500',
            ]);

            $cita = DB::table('citas')
                ->where('id', $id)
                ->where('paciente_id', $pacienteId)
                ->first();

            if (!$cita) {
                return response()->json(['message' => 'Cita no encontrada'], 404);
            }

            if (!$cita->rating) {
                return response()->json([
                    'message' => 'Esta cita no tiene una calificación. Usa POST para crear una.'
                ], 422);
            }

            DB::table('citas')
                ->where('id', $id)
                ->update([
                    'rating' => $data['rating'],
                    'review' => $data['review'] ?? null,
                    'rated_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

            return response()->json([
                'message' => 'Calificación actualizada correctamente',
                'rating' => $data['rating'],
                'review' => $data['review'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al actualizar calificación', [
                'user_id' => $request->user()?->id,
                'cita_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al actualizar la calificación',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Obtener la calificación de una cita
     * GET /api/paciente/citas/{id}/rating
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

            $cita = DB::table('citas')
                ->where('id', $id)
                ->where('paciente_id', $pacienteId)
                ->select('rating', 'review', 'rated_at', 'estado')
                ->first();

            if (!$cita) {
                return response()->json(['message' => 'Cita no encontrada'], 404);
            }

            return response()->json([
                'rating' => $cita->rating,
                'review' => $cita->review,
                'rated_at' => $cita->rated_at,
                'puede_calificar' => $cita->estado === 'completada' && !$cita->rating,
                'puede_editar' => $cita->estado === 'completada' && $cita->rating !== null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al obtener calificación', [
                'user_id' => $request->user()?->id,
                'cita_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error al obtener la calificación',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }
}