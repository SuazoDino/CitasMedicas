<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Medico;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MedicoController extends Controller
{
    public function especialidades()
    {
        return response()->json(Especialidad::orderBy('nombre')->get(['id', 'nombre', 'slug']));
    }

    public function index(Request $request)
    {
        $query = Medico::query()
            ->where('is_searchable', true)
            ->where('verif_status', 'verificado')
            ->with(['usuario:id,email', 'especialidades:id,nombre,slug'])
            ->withCount(['resenas as resenas_count' => fn ($q) => $q->where('estado', 'aprobada')])
            ->withAvg(['resenas as calificacion_promedio' => fn ($q) => $q->where('estado', 'aprobada')], 'calificacion');

        if ($request->filled('especialidad_id')) {
            $especialidadId = $request->especialidad_id;
            $query->whereHas('especialidades', fn ($q) => $q->where('especialidades.id', $especialidadId));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('usuario', fn ($qq) => $qq->where('email', 'like', "%$search%"))
                  ->orWhereHas('especialidades', fn ($qq) => $qq->where('nombre', 'like', "%$search%"));
            });
        }

        return response()->json($query->paginate(12));
    }

    public function show($id)
    {
        $medico = Medico::where('is_searchable', true)
            ->with(['usuario:id,email', 'especialidades:id,nombre,slug'])
            ->withCount(['resenas as resenas_count' => fn ($q) => $q->where('estado', 'aprobada')])
            ->withAvg(['resenas as calificacion_promedio' => fn ($q) => $q->where('estado', 'aprobada')], 'calificacion')
            ->findOrFail($id);

        $medico->load(['resenas' => fn ($q) => $q->where('estado', 'aprobada')
            ->with('paciente.usuario:id,email')
            ->latest()
            ->limit(10)]);

        return response()->json($medico);
    }

    public function disponibilidad(Request $request, $id)
    {
        $request->validate(['fecha' => 'required|date']);

        $medico = Medico::findOrFail($id);
        $fecha = Carbon::parse($request->fecha)->startOfDay();
        $diaSemana = $fecha->dayOfWeekIso;

        $horarios = $medico->horarios()->where('dia_semana', $diaSemana)->where('activo', true)->get();

        if ($horarios->isEmpty()) {
            return response()->json(['slots' => []]);
        }

        $ocupados = Cita::where('medico_id', $medico->id)
            ->whereBetween('starts_at', [$fecha->copy(), $fecha->copy()->endOfDay()])
            ->where('estado', '!=', 'cancelada')
            ->pluck('starts_at')
            ->map(fn ($dt) => $dt->format('Y-m-d H:i:s'))
            ->all();

        $slots = [];
        foreach ($horarios as $horario) {
            $duracion = $horario->slot_min ?: 30;
            $cursor = Carbon::parse($fecha->format('Y-m-d') . ' ' . $horario->hora_inicio);
            $fin = Carbon::parse($fecha->format('Y-m-d') . ' ' . $horario->hora_fin);

            while ($cursor->copy()->addMinutes($duracion)->lte($fin)) {
                $termina = $cursor->copy()->addMinutes($duracion);
                if (!in_array($cursor->format('Y-m-d H:i:s'), $ocupados) && $cursor->isFuture()) {
                    $slots[] = [
                        'inicio' => $cursor->format('Y-m-d H:i:s'),
                        'fin' => $termina->format('Y-m-d H:i:s'),
                        'hora_label' => $cursor->format('H:i'),
                    ];
                }
                $cursor = $termina;
            }
        }

        return response()->json(['slots' => $slots]);
    }
}
