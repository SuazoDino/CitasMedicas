<?php

namespace App\Services;

use App\Models\Paciente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PacienteDashboardService
{
    public function buildResumen(Paciente $paciente): array
    {
        $now = Carbon::now();

        $statsRow = DB::table('citas')
            ->selectRaw("SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as completadas")
            ->selectRaw("SUM(CASE WHEN starts_at < ? THEN 1 ELSE 0 END) as historial", [$now])
            ->selectRaw("SUM(CASE WHEN starts_at >= ? THEN 1 ELSE 0 END) as proximas", [$now])
            ->where('paciente_id', $paciente->id)
            ->first();

        $stats = [
            'proximas'     => (int) ($statsRow->proximas ?? 0),
            'completadas'  => (int) ($statsRow->completadas ?? 0),
            'historial'    => (int) ($statsRow->historial ?? 0),
            'favoritos'    => (int) $paciente->favoritos()->count(),
        ];

        $historial = DB::table('citas as c')
            ->join('medicos as m', 'm.id', '=', 'c.medico_id')
            ->join('users as u', 'u.id', '=', 'm.user_id')
            ->leftJoin('especialidades as e', 'e.id', '=', 'c.especialidad_id')
            ->where('c.paciente_id', $paciente->id)
            ->where('c.starts_at', '<', $now)
            ->orderBy('c.starts_at', 'desc')
            ->limit(5)
            ->get([
                'c.id', 'c.starts_at', 'c.estado',
                'u.name as medico', 'e.nombre as especialidad',
            ])
            ->map(function ($row) {
                $start = Carbon::parse($row->starts_at);

                return [
                    'id'           => $row->id,
                    'fecha'        => $start->format('Y-m-d'),
                    'hora'         => $start->format('H:i'),
                    'estado'       => $row->estado,
                    'medico'       => $row->medico,
                    'especialidad' => $row->especialidad,
                ];
            })
            ->all();

        $recomendados = $this->buildRecomendados($paciente);
        $tips = $this->buildTips($stats, $historial);

        return [
            'stats'        => $stats,
            'historial'    => $historial,
            'recomendados' => $recomendados,
            'tips'         => $tips,
        ];
    }

    /**
     * Genera recomendaciones de especialistas priorizando las especialidades
     * que el paciente consulta con mayor frecuencia.
     */
    protected function buildRecomendados(Paciente $paciente): array
    {
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

        $fallback = DB::table('medico_especialidad')
            ->select('especialidad_id', DB::raw('COUNT(*) as total'))
            ->groupBy('especialidad_id')
            ->orderByDesc('total')
            ->limit(3)
            ->pluck('especialidad_id')
            ->filter()
            ->all();

        $especialidades = array_values(array_unique(array_merge($preferidas, $fallback)));

        if (empty($especialidades)) {
            return [];
        }

        $favoritosIds = $paciente->favoritos()->pluck('medicos.id')->all();

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

        if ($medicos->isEmpty()) {
            return [];
        }

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