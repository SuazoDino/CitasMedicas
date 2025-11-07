<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogoController extends Controller
{
    
    public function especialidades()
    {
        return DB::table('especialidades')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'slug']);
    }

    public function medicos(Request $request)
    {
        $query = DB::table('medico_especialidad as me')
            ->join('medicos as m', 'm.id', '=', 'me.medico_id')
            ->join('users as u', 'u.id', '=', 'm.user_id')
            ->join('especialidades as e', 'e.id', '=', 'me.especialidad_id')
            ->where('m.is_searchable', true)
            ->select([
                'm.id as id',
                'u.name as nombre',
                'me.especialidad_id',
                'e.nombre as especialidad_nombre',
                'e.slug as especialidad_slug',
            ])
            ->distinct()
            ->orderBy('u.name');

        // Búsqueda por nombre del médico
        if ($request->filled('q')) {
            $searchTerm = $request->string('q');
            $query->where(function($q) use ($searchTerm) {
                $q->where('u.name', 'like', "%{$searchTerm}%")
                  ->orWhere('e.nombre', 'like', "%{$searchTerm}%");
            });
        }

        // Filtro por especialidad
        if ($request->filled('especialidad_id')) {
            $query->where('me.especialidad_id', $request->integer('especialidad_id'));
        }

        return $query->get()->map(function($medico) {
            return [
                'id' => $medico->id,
                'nombre' => $medico->nombre,
                'especialidad_id' => $medico->especialidad_id,
                'especialidad_nombre' => $medico->especialidad_nombre,
                'especialidad_slug' => $medico->especialidad_slug,
            ];
        });
    }
    
    public function search(Request $request)
    {
        // Inicializar respuesta por defecto
        $response = [
            'medicos' => [],
            'especialidades' => [],
        ];
        
        // Obtener query de forma segura
        $q = '';
        try {
            $q = trim((string) $request->input('q', ''));
        } catch (\Throwable $e) {
            return response()->json($response, 200);
        }
        
        if (empty($q) || strlen($q) < 1) {
            return response()->json($response, 200);
        }
        
        // Sanitizar query para prevenir SQL injection
        $q = str_replace(['%', '_'], ['\%', '\_'], $q);
        
        // Buscar especialidades
        try {
            $especialidades = DB::table('especialidades')
                ->where('nombre', 'like', "%{$q}%")
                ->select('id', 'nombre', 'slug')
                ->limit(10)
                ->get();
            
            $response['especialidades'] = $especialidades->map(function($esp) {
                return [
                    'id' => (int) ($esp->id ?? 0),
                    'nombre' => (string) ($esp->nombre ?? ''),
                    'slug' => (string) ($esp->slug ?? ''),
                ];
            })->values()->all();
        } catch (\Throwable $e) {
            // Silenciar error, mantener array vacío
            $response['especialidades'] = [];
        }
        
        // Buscar médicos - intentar con is_searchable primero
        try {
            $medicosQuery = DB::table('medico_especialidad as me')
                ->join('medicos as m', 'm.id', '=', 'me.medico_id')
                ->join('users as u', 'u.id', '=', 'm.user_id')
                ->join('especialidades as e', 'e.id', '=', 'me.especialidad_id')
                ->where(function($query) use ($q) {
                    $query->where('u.name', 'like', "%{$q}%")
                          ->orWhere('e.nombre', 'like', "%{$q}%");
                });
            
            // Intentar agregar filtro is_searchable
            try {
                $medicosQuery->where('m.is_searchable', true);
            } catch (\Throwable $e) {
                // Si falla, continuar sin el filtro
            }
            
            $medicos = $medicosQuery
                ->select([
                    'm.id as id',
                    'u.name as nombre',
                    'e.id as especialidad_id',
                    'e.nombre as especialidad_nombre',
                    'e.slug as especialidad_slug',
                ])
                ->distinct()
                ->limit(20)
                ->get();
            
            $response['medicos'] = $medicos->map(function($medico) {
                return [
                    'id' => (int) ($medico->id ?? 0),
                    'nombre' => (string) ($medico->nombre ?? ''),
                    'especialidad_id' => (int) ($medico->especialidad_id ?? 0),
                    'especialidad_nombre' => (string) ($medico->especialidad_nombre ?? ''),
                    'especialidad_slug' => (string) ($medico->especialidad_slug ?? ''),
                ];
            })->values()->all();
        } catch (\Throwable $e) {
            // Si falla con is_searchable, intentar sin ese filtro
            try {
                $medicos = DB::table('medico_especialidad as me')
                    ->join('medicos as m', 'm.id', '=', 'me.medico_id')
                    ->join('users as u', 'u.id', '=', 'm.user_id')
                    ->join('especialidades as e', 'e.id', '=', 'me.especialidad_id')
                    ->where(function($query) use ($q) {
                        $query->where('u.name', 'like', "%{$q}%")
                              ->orWhere('e.nombre', 'like', "%{$q}%");
                    })
                    ->select([
                        'm.id as id',
                        'u.name as nombre',
                        'e.id as especialidad_id',
                        'e.nombre as especialidad_nombre',
                        'e.slug as especialidad_slug',
                    ])
                    ->distinct()
                    ->limit(20)
                    ->get();
                
                $response['medicos'] = $medicos->map(function($medico) {
                    return [
                        'id' => (int) ($medico->id ?? 0),
                        'nombre' => (string) ($medico->nombre ?? ''),
                        'especialidad_id' => (int) ($medico->especialidad_id ?? 0),
                        'especialidad_nombre' => (string) ($medico->especialidad_nombre ?? ''),
                        'especialidad_slug' => (string) ($medico->especialidad_slug ?? ''),
                    ];
                })->values()->all();
            } catch (\Throwable $e2) {
                // Si todo falla, mantener array vacío
                $response['medicos'] = [];
            }
        }
        
        // SIEMPRE devolver respuesta válida
        return response()->json($response, 200);
    }
    
    public function medicoPerfil($id)
    {
        try {
            // Obtener información básica del médico
            $medico = DB::table('medicos as m')
                ->join('users as u', 'u.id', '=', 'm.user_id')
                ->where('m.id', $id)
                ->select([
                    'm.id',
                    'm.verif_status',
                    'm.verified_at',
                    'u.name as nombre',
                    'u.email',
                    'u.phone',
                ])
                ->first();
            
            if (!$medico) {
                return response()->json(['message' => 'Médico no encontrado'], 404);
            }
            
            // Obtener especialidades
            $especialidades = DB::table('medico_especialidad as me')
                ->join('especialidades as e', 'e.id', '=', 'me.especialidad_id')
                ->where('me.medico_id', $id)
                ->select('e.id', 'e.nombre', 'e.slug')
                ->get();
            
            // Estadísticas
            $stats = [
                'total_citas' => DB::table('citas')->where('medico_id', $id)->count(),
                'citas_completadas' => DB::table('citas')->where('medico_id', $id)->where('estado', 'completada')->count(),
                'citas_pendientes' => DB::table('citas')->where('medico_id', $id)->where('estado', 'pendiente')->count(),
                'pacientes_atendidos' => (int) DB::table('citas')->where('medico_id', $id)->where('estado', 'completada')->distinct()->count('paciente_id'),
                'seguidores' => DB::table('paciente_favoritos')->where('medico_id', $id)->count(),
            ];
            
            // Reseñas basadas en citas completadas (simuladas)
            $citasCompletadas = DB::table('citas as c')
                ->join('pacientes as p', 'p.id', '=', 'c.paciente_id')
                ->join('users as u', 'u.id', '=', 'p.user_id')
                ->where('c.medico_id', $id)
                ->where('c.estado', 'completada')
                ->select([
                    'c.id',
                    'c.starts_at',
                    'c.notas',
                    'u.name as paciente_nombre',
                ])
                ->orderBy('c.starts_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($cita) {
                    // Simular rating basado en la fecha (para demo)
                    $rating = 3 + (($cita->id % 3)); // 3, 4 o 5
                    return [
                        'id' => $cita->id,
                        'paciente' => $cita->paciente_nombre,
                        'fecha' => $cita->starts_at,
                        'rating' => $rating,
                        'comentario' => $cita->notas ? substr($cita->notas, 0, 200) : 'Excelente atención profesional.',
                    ];
                });
            
            // Calcular rating promedio
            $ratingPromedio = $citasCompletadas->count() > 0 
                ? round($citasCompletadas->avg('rating'), 1) 
                : 0;
            
            return response()->json([
                'medico' => [
                    'id' => (int) $medico->id,
                    'nombre' => $medico->nombre,
                    'email' => $medico->email,
                    'phone' => $medico->phone,
                    'verif_status' => $medico->verif_status,
                    'verified_at' => $medico->verified_at,
                ],
                'especialidades' => $especialidades,
                'stats' => $stats,
                'rating' => [
                    'promedio' => $ratingPromedio,
                    'total_resenas' => $citasCompletadas->count(),
                ],
                'resenas' => $citasCompletadas,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error al obtener perfil de médico', [
                'medico_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Error al cargar el perfil del médico',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
