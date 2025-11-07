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
        try {
            $q = $request->string('q', '')->trim();
            
            if (empty($q)) {
                return response()->json([
                    'medicos' => [],
                    'especialidades' => [],
                ]);
            }
            
            $medicos = [];
            $especialidades = [];
            
            // Verificar que las tablas existan
            $schema = DB::getSchemaBuilder();
            $hasMedicoEspecialidad = $schema->hasTable('medico_especialidad');
            $hasMedicos = $schema->hasTable('medicos');
            $hasEspecialidades = $schema->hasTable('especialidades');
            
            if (!$hasMedicos || !$hasEspecialidades) {
                \Log::warning('Tablas faltantes para búsqueda', [
                    'has_medicos' => $hasMedicos,
                    'has_especialidades' => $hasEspecialidades,
                ]);
                return response()->json([
                    'medicos' => [],
                    'especialidades' => [],
                ]);
            }
            
            // Buscar médicos solo si existe la tabla pivot
            if ($hasMedicoEspecialidad) {
                try {
                    // Verificar si la columna is_searchable existe
                    $hasIsSearchable = $schema->hasColumn('medicos', 'is_searchable');
                    
                    // Buscar médicos
                    $medicosQuery = DB::table('medico_especialidad as me')
                        ->join('medicos as m', 'm.id', '=', 'me.medico_id')
                        ->join('users as u', 'u.id', '=', 'm.user_id')
                        ->join('especialidades as e', 'e.id', '=', 'me.especialidad_id')
                        ->where(function($query) use ($q) {
                            $query->where('u.name', 'like', "%{$q}%")
                                  ->orWhere('e.nombre', 'like', "%{$q}%");
                        });
                    
                    // Solo filtrar por is_searchable si la columna existe
                    if ($hasIsSearchable) {
                        $medicosQuery->where('m.is_searchable', true);
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
                        ->get()
                        ->map(function($medico) {
                            return [
                                'id' => $medico->id,
                                'nombre' => $medico->nombre,
                                'especialidad_id' => $medico->especialidad_id,
                                'especialidad_nombre' => $medico->especialidad_nombre,
                                'especialidad_slug' => $medico->especialidad_slug,
                            ];
                        })
                        ->toArray();
                } catch (\Exception $e) {
                    \Log::warning('Error al buscar médicos', [
                        'error' => $e->getMessage(),
                    ]);
                    $medicos = [];
                }
            }
            
            // Buscar especialidades
            try {
                $especialidades = DB::table('especialidades')
                    ->where('nombre', 'like', "%{$q}%")
                    ->select('id', 'nombre', 'slug')
                    ->limit(10)
                    ->get()
                    ->toArray();
            } catch (\Exception $e) {
                \Log::warning('Error al buscar especialidades', [
                    'error' => $e->getMessage(),
                ]);
                $especialidades = [];
            }
            
            return response()->json([
                'medicos' => $medicos,
                'especialidades' => $especialidades,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en búsqueda de catálogo', [
                'query' => $request->string('q', ''),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'medicos' => [],
                'especialidades' => [],
                'error' => config('app.debug') ? $e->getMessage() : 'Error al realizar la búsqueda',
            ], 200); // Cambiar a 200 para que el frontend pueda manejar el error
        }
    }
}
