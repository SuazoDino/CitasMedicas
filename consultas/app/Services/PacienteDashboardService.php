<?php

namespace App\Services;

use App\Models\Paciente;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PacienteDashboardService
{
    public function buildResumen(Paciente $paciente): array
    {
        // Retornar valores por defecto inmediatamente si hay algún problema
        $defaultResponse = [
            'stats' => [
                'proximas' => 0,
                'completadas' => 0,
                'historial' => 0,
                'favoritos' => 0,
            ],
            'historial' => [],
            'recomendados' => [],
            'tips' => ['Bienvenido a MediReserva. Comienza agendando tu primera cita.'],
        ];

        try {
            $now = Carbon::now();

            // Stats básicas - manejar caso donde no hay citas
            try {
                $statsRow = DB::table('citas')
                    ->selectRaw("COALESCE(SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END), 0) as completadas")
                    ->selectRaw("COALESCE(SUM(CASE WHEN starts_at < ? THEN 1 ELSE 0 END), 0) as historial", [$now])
                    ->selectRaw("COALESCE(SUM(CASE WHEN starts_at >= ? THEN 1 ELSE 0 END), 0) as proximas", [$now])
                    ->where('paciente_id', $paciente->id)
                    ->first();

                $stats = [
                    'proximas'     => (int) ($statsRow->proximas ?? 0),
                    'completadas'  => (int) ($statsRow->completadas ?? 0),
                    'historial'    => (int) ($statsRow->historial ?? 0),
                    'favoritos'    => 0,
                ];
            } catch (\Exception $e) {
                Log::warning('Error al obtener stats: ' . $e->getMessage());
                $stats = $defaultResponse['stats'];
            }
            
            // Contar favoritos de forma segura
            try {
                if (DB::getSchemaBuilder()->hasTable('paciente_favoritos')) {
                    $stats['favoritos'] = (int) DB::table('paciente_favoritos')
                        ->where('paciente_id', $paciente->id)
                        ->count();
                }
            } catch (\Exception $e) {
                Log::warning('No se pudo contar favoritos: ' . $e->getMessage());
            }

            // Historial - manejar caso donde no hay citas o médicos
            $historial = [];
            try {
                if (DB::getSchemaBuilder()->hasTable('citas') && 
                    DB::getSchemaBuilder()->hasTable('medicos') && 
                    DB::getSchemaBuilder()->hasTable('users')) {
                    
                    $historialRows = DB::table('citas as c')
                        ->leftJoin('medicos as m', 'm.id', '=', 'c.medico_id')
                        ->leftJoin('users as u', 'u.id', '=', 'm.user_id')
                        ->leftJoin('especialidades as e', 'e.id', '=', 'c.especialidad_id')
                        ->where('c.paciente_id', $paciente->id)
                        ->where('c.starts_at', '<', $now)
                        ->orderBy('c.starts_at', 'desc')
                        ->limit(5)
                        ->get([
                            'c.id', 'c.starts_at', 'c.estado',
                            'u.name as medico', 'e.nombre as especialidad',
                        ]);

                    $historial = $historialRows->map(function ($row) {
                        if (!$row->starts_at) {
                            return null;
                        }
                        try {
                            $start = Carbon::parse($row->starts_at);
                            return [
                                'id'           => $row->id,
                                'fecha'        => $start->format('Y-m-d'),
                                'hora'         => $start->format('H:i'),
                                'estado'       => $row->estado ?? 'pendiente',
                                'medico'       => $row->medico ?? 'Médico',
                                'especialidad' => $row->especialidad ?? null,
                            ];
                        } catch (\Exception $e) {
                            Log::warning('Error al parsear fecha de cita: ' . $e->getMessage());
                            return null;
                        }
                    })->filter()->values()->all();
                }
            } catch (\Exception $e) {
                Log::warning('No se pudo obtener historial: ' . $e->getMessage());
            }

            // Recomendados - si falla, retornar array vacío
            $recomendados = [];
            try {
                $recomendados = $this->buildRecomendados($paciente);
            } catch (\Exception $e) {
                Log::warning('Error al obtener recomendados: ' . $e->getMessage());
            }

            // Tips
            $tips = [];
            try {
                $tips = $this->buildTips($stats, $historial);
            } catch (\Exception $e) {
                Log::warning('Error al obtener tips: ' . $e->getMessage());
                $tips = $defaultResponse['tips'];
            }

            return [
                'stats'        => $stats,
                'historial'    => $historial,
                'recomendados' => $recomendados,
                'tips'         => $tips,
            ];
        } catch (\Throwable $e) {
            Log::error('Error en buildResumen: ' . $e->getMessage(), [
                'paciente_id' => $paciente->id ?? null,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Retornar valores por defecto en caso de error
            return $defaultResponse;
        }
    }

    protected function buildRecomendados(Paciente $paciente): array
    {
        try {
            // Verificar que las tablas necesarias existan
            if (!DB::getSchemaBuilder()->hasTable('citas') || 
                !DB::getSchemaBuilder()->hasTable('medico_especialidad') ||
                !DB::getSchemaBuilder()->hasTable('medicos') ||
                !DB::getSchemaBuilder()->hasTable('users') ||
                !DB::getSchemaBuilder()->hasTable('especialidades')) {
                return [];
            }

            $preferidas = DB::table('citas')
                ->select('especialidad_id', DB::raw('COUNT(*) as total'))
                ->where('paciente_id', $paciente->id)
                ->whereNotNull('especialidad_id')
                ->whereIn('estado', ['confirmada', 'completada'])
                ->groupBy('especialidad_id')
                ->orderByDesc('total')
                ->limit(3)
                ->pluck('especialidad_id')
                ->filter()
                ->all();

            $fallback = [];
            try {
                $fallback = DB::table('medico_especialidad')
                    ->select('especialidad_id', DB::raw('COUNT(*) as total'))
                    ->groupBy('especialidad_id')
                    ->orderByDesc('total')
                    ->limit(3)
                    ->pluck('especialidad_id')
                    ->filter()
                    ->all();
            } catch (\Exception $e) {
                // Si la tabla no existe o hay error, continuar sin fallback
                Log::warning('No se pudo obtener especialidades fallback: ' . $e->getMessage());
            }

            $especialidades = array_values(array_unique(array_merge($preferidas, $fallback)));

            if (empty($especialidades)) {
                return [];
            }

            $favoritosIds = [];
            try {
                if (DB::getSchemaBuilder()->hasTable('paciente_favoritos')) {
                    $favoritosIds = DB::table('paciente_favoritos')
                        ->where('paciente_id', $paciente->id)
                        ->pluck('medico_id')
                        ->all();
                }
            } catch (\Exception $e) {
                Log::warning('No se pudieron obtener favoritos: ' . $e->getMessage());
            }

            $medicos = Collection::make([]);
            try {
                $medicos = DB::table('medicos as m')
                    ->join('users as u', 'u.id', '=', 'm.user_id')
                    ->join('medico_especialidad as me', 'me.medico_id', '=', 'm.id')
                    ->join('especialidades as e', 'e.id', '=', 'me.especialidad_id')
                    ->whereIn('me.especialidad_id', $especialidades)
                    ->when(!empty($favoritosIds), fn ($q) => $q->whereNotIn('m.id', $favoritosIds))
                    ->where(function ($q) {
                        $q->where('m.is_searchable', true)->orWhereNull('m.is_searchable');
                    })
                    ->select('m.id', 'u.name as nombre', 'e.nombre as especialidad')
                    ->distinct()
                    ->limit(6)
                    ->get();
            } catch (\Exception $e) {
                Log::warning('No se pudieron obtener médicos recomendados: ' . $e->getMessage());
                return [];
            }

            if ($medicos->isEmpty()) {
                return [];
            }

            $stats = Collection::make([]);
            try {
                $stats = DB::table('citas')
                    ->select(
                        'medico_id',
                        DB::raw('COUNT(*) as total'),
                        DB::raw("SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as completadas")
                    )
                    ->whereIn('medico_id', $medicos->pluck('id'))
                    ->groupBy('medico_id')
                    ->get()
                    ->keyBy('medico_id');
            } catch (\Exception $e) {
                Log::warning('No se pudieron obtener estadísticas de médicos: ' . $e->getMessage());
            }

            return $medicos->map(function ($medico) use ($stats) {
                $raw = $stats->get($medico->id);
                $total = (int) ($raw->total ?? 0);
                $completadas = (int) ($raw->completadas ?? 0);
                $ratio = $total > 0 ? $completadas / max(1, $total) : 0;
                $rating = round(4.2 + min(0.8, $ratio * 0.8), 1);

                return [
                    'id'           => $medico->id,
                    'nombre'       => $medico->nombre,
                    'especialidad' => $medico->especialidad,
                    'rating'       => $rating,
                    'reviews'      => $total,
                ];
            })->values()->all();
        } catch (\Exception $e) {
            Log::error('Error en buildRecomendados: ' . $e->getMessage(), [
                'paciente_id' => $paciente->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    protected function buildTips(array $stats, array $historial): array
    {
        $tips = [];

        if (($stats['proximas'] ?? 0) === 0) {
            $tips[] = 'No tienes citas próximas. Programa una consulta preventiva para mantenerte al día.';
        }

        if (($stats['favoritos'] ?? 0) === 0) {
            $tips[] = 'Agrega médicos a tu lista de favoritos para reservar más rápido la próxima vez.';
        }

        if (!empty($historial)) {
            $tips[] = 'Revisa el detalle de tus últimas consultas y solicita seguimientos si los necesitas.';
        }

        if (empty($tips)) {
            $tips[] = 'Mantén tus datos personales actualizados para recibir recordatorios y recomendaciones personalizadas.';
        }

        return array_values(array_unique($tips));
    }
}