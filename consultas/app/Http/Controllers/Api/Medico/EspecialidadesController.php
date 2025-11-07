<?php

namespace App\Http\Controllers\Api\Medico;

use App\Http\Controllers\Controller;
use App\Models\Medico;
use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EspecialidadesController extends Controller
{
    /**
     * Obtener las especialidades del médico autenticado
     * GET /api/medico/especialidades
     */
    public function index(Request $request)
    {
        Log::info('EspecialidadesController@index llamado', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $request->user()?->id,
        ]);

        try {
            $user = $request->user();
            if (!$user) {
                Log::warning('Usuario no autenticado en EspecialidadesController@index');
                return response()->json(['message' => 'Usuario no autenticado'], 401);
            }

            Log::info('Usuario autenticado, buscando médico', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            // Usar consulta directa como otros controladores para ser consistente
            $medicoId = DB::table('medicos')->where('user_id', $user->id)->value('id');
            
            // Log adicional para debugging
            $allMedicos = DB::table('medicos')->select('id', 'user_id')->get();
            Log::info('Todos los médicos en BD', [
                'total' => $allMedicos->count(),
                'medicos' => $allMedicos->toArray(),
                'buscando_user_id' => $user->id,
            ]);
            
            if (!$medicoId) {
                Log::warning('No se encontró perfil de médico, devolviendo array vacío', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'medico_id_encontrado' => $medicoId,
                ]);
                // En lugar de devolver 404, devolvemos un array vacío de especialidades
                // Esto permite que el frontend funcione aunque el perfil no esté completo
                return response()->json([
                    'medico_id' => null,
                    'especialidades' => [],
                ]);
            }
            
            Log::info('Médico encontrado, obteniendo especialidades', [
                'medico_id' => $medicoId,
            ]);

            // Obtener las especialidades usando consulta directa para evitar problemas con la relación
            $especialidades = DB::table('medico_especialidad as me')
                ->join('especialidades as e', 'e.id', '=', 'me.especialidad_id')
                ->where('me.medico_id', $medicoId)
                ->select('e.id', 'e.nombre', 'e.slug')
                ->get();
            
            // Log adicional para ver qué especialidades hay en la BD
            $allEspecialidadesMedico = DB::table('medico_especialidad')
                ->where('medico_id', $medicoId)
                ->get();
            Log::info('Especialidades del médico en BD', [
                'medico_id' => $medicoId,
                'total_en_pivot' => $allEspecialidadesMedico->count(),
                'pivot_data' => $allEspecialidadesMedico->toArray(),
            ]);

            Log::info('Especialidades obtenidas', [
                'medico_id' => $medicoId,
                'count' => $especialidades->count(),
                'especialidades' => $especialidades->toArray(),
            ]);

            return response()->json([
                'medico_id' => $medicoId,
                'especialidades' => $especialidades,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener especialidades del médico', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error al cargar las especialidades',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Actualizar las especialidades del médico autenticado
     * PUT /api/medico/especialidades
     */
    public function update(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $medico = Medico::where('user_id', $user->id)->first();
        if (!$medico) {
            return response()->json(['message' => 'No se encontró el perfil de médico'], 404);
        }

        $data = $request->validate([
            'especialidades' => ['required', 'array', 'min:1'],
            'especialidades.*' => ['required', 'integer', 'exists:especialidades,id'],
        ]);

        try {
            // Eliminar todas las especialidades existentes
            DB::table('medico_especialidad')
                ->where('medico_id', $medico->id)
                ->delete();

            // Insertar las nuevas especialidades
            if (!empty($data['especialidades'])) {
                $inserts = array_map(function($espId) use ($medico) {
                    return [
                        'medico_id' => $medico->id,
                        'especialidad_id' => $espId,
                    ];
                }, $data['especialidades']);

                DB::table('medico_especialidad')->insert($inserts);
            }

            Log::info('Especialidades actualizadas para médico', [
                'medico_id' => $medico->id,
                'user_id' => $user->id,
                'especialidades' => $data['especialidades'],
            ]);

            // Obtener todas las especialidades usando consulta directa
            $especialidades = DB::table('medico_especialidad as me')
                ->join('especialidades as e', 'e.id', '=', 'me.especialidad_id')
                ->where('me.medico_id', $medico->id)
                ->select('e.id', 'e.nombre', 'e.slug')
                ->get();

            return response()->json([
                'message' => 'Especialidades actualizadas correctamente',
                'medico_id' => $medico->id,
                'especialidades' => $especialidades,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar especialidades del médico', [
                'medico_id' => $medico->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error al actualizar las especialidades',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Agregar una especialidad al médico autenticado
     * POST /api/medico/especialidades
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Usuario no autenticado'], 401);
            }

            $medico = Medico::where('user_id', $user->id)->first();
            if (!$medico) {
                Log::warning('No se encontró perfil de médico en store', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
                return response()->json(['message' => 'No se encontró el perfil de médico. Por favor, completa tu registro de médico primero.'], 404);
            }

            $data = $request->validate([
                'especialidad_id' => ['required', 'integer', 'exists:especialidades,id'],
            ]);

            Log::info('Intentando agregar especialidad', [
                'medico_id' => $medico->id,
                'especialidad_id' => $data['especialidad_id'],
                'user_id' => $user->id,
            ]);

            // Verificar si ya tiene esta especialidad usando consulta directa
            $existe = DB::table('medico_especialidad')
                ->where('medico_id', $medico->id)
                ->where('especialidad_id', $data['especialidad_id'])
                ->exists();
            
            if ($existe) {
                return response()->json([
                    'message' => 'El médico ya tiene esta especialidad asignada',
                ], 422);
            }

            // Agregar la especialidad usando consulta directa
            $insertado = DB::table('medico_especialidad')->insert([
                'medico_id' => $medico->id,
                'especialidad_id' => $data['especialidad_id'],
            ]);

            Log::info('Especialidad insertada en BD', [
                'medico_id' => $medico->id,
                'especialidad_id' => $data['especialidad_id'],
                'insertado' => $insertado,
            ]);

            // Verificar que se insertó correctamente
            $verificacion = DB::table('medico_especialidad')
                ->where('medico_id', $medico->id)
                ->where('especialidad_id', $data['especialidad_id'])
                ->exists();

            Log::info('Verificación después de insertar', [
                'medico_id' => $medico->id,
                'especialidad_id' => $data['especialidad_id'],
                'existe_en_bd' => $verificacion,
            ]);

            // Obtener todas las especialidades usando consulta directa
            $especialidades = DB::table('medico_especialidad as me')
                ->join('especialidades as e', 'e.id', '=', 'me.especialidad_id')
                ->where('me.medico_id', $medico->id)
                ->select('e.id', 'e.nombre', 'e.slug')
                ->get();

            Log::info('Especialidades obtenidas después de insertar', [
                'medico_id' => $medico->id,
                'count' => $especialidades->count(),
            ]);

            return response()->json([
                'message' => 'Especialidad agregada correctamente',
                'medico_id' => $medico->id,
                'especialidades' => $especialidades,
            ], 201);
        } catch (\Exception $e) {
            $medicoId = null;
            try {
                $medicoId = Medico::where('user_id', $request->user()?->id)->value('id');
            } catch (\Exception $e2) {
                // Ignorar
            }

            Log::error('Error al agregar especialidad al médico', [
                'user_id' => $request->user()?->id,
                'medico_id' => $medicoId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error al agregar la especialidad',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Eliminar una especialidad del médico autenticado
     * DELETE /api/medico/especialidades/{especialidad}
     */
    public function destroy(Request $request, $especialidadId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $medico = Medico::where('user_id', $user->id)->first();
        if (!$medico) {
            return response()->json(['message' => 'No se encontró el perfil de médico'], 404);
        }

        // Verificar que la especialidad existe
        $especialidad = Especialidad::find($especialidadId);
        if (!$especialidad) {
            return response()->json(['message' => 'Especialidad no encontrada'], 404);
        }

        try {
            // Verificar si el médico tiene esta especialidad usando consulta directa
            $existe = DB::table('medico_especialidad')
                ->where('medico_id', $medico->id)
                ->where('especialidad_id', $especialidadId)
                ->exists();
            
            if (!$existe) {
                return response()->json([
                    'message' => 'El médico no tiene esta especialidad asignada',
                ], 422);
            }

            // Eliminar la especialidad usando consulta directa
            DB::table('medico_especialidad')
                ->where('medico_id', $medico->id)
                ->where('especialidad_id', $especialidadId)
                ->delete();

            Log::info('Especialidad eliminada del médico', [
                'medico_id' => $medico->id,
                'especialidad_id' => $especialidadId,
            ]);

            // Obtener todas las especialidades usando consulta directa
            $especialidades = DB::table('medico_especialidad as me')
                ->join('especialidades as e', 'e.id', '=', 'me.especialidad_id')
                ->where('me.medico_id', $medico->id)
                ->select('e.id', 'e.nombre', 'e.slug')
                ->get();

            return response()->json([
                'message' => 'Especialidad eliminada correctamente',
                'medico_id' => $medico->id,
                'especialidades' => $especialidades,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar especialidad del médico', [
                'medico_id' => $medico->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error al eliminar la especialidad',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor',
            ], 500);
        }
    }
}



