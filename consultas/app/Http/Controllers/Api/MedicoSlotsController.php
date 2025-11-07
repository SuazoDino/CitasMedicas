<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicoSlotsController extends Controller
{
    /**
     * Lista los slots disponibles (y ocupados) para un médico entre dos fechas.
     */
    public function slots(Request $request, int $medicoId)
    {
        $timezone = config('app.timezone', 'UTC');
        $desdeInput = $request->query('desde');
        $hastaInput = $request->query('hasta');

        $now = Carbon::now($timezone);
        $desde = $desdeInput
            ? Carbon::parse($desdeInput, $timezone)->startOfDay()
            : $now->copy()->startOfDay();
        
        // Asegurar que 'desde' no sea una fecha pasada
        if ($desde->lt($now->copy()->startOfDay())) {
            $desde = $now->copy()->startOfDay();
        }

        $hasta = $hastaInput
            ? Carbon::parse($hastaInput, $timezone)->endOfDay()
            : $desde->copy()->addDays(6)->endOfDay();

        if ($hasta->lt($desde)) {
            [$desde, $hasta] = [
                $hasta->copy()->startOfDay(),
                $desde->copy()->endOfDay(),
            ];
        }

        $horarios = DB::table('medico_horarios')
            ->where('medico_id', $medicoId)
            ->where('activo', true)
            ->get(['dia_semana', 'hora_inicio', 'hora_fin', 'slot_min']);

        if ($horarios->isEmpty()) {
            return response()->json([]);
        }

        $citas = DB::table('citas')
            ->where('medico_id', $medicoId)
            ->whereBetween('starts_at', [$desde, $hasta])
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->get(['starts_at', 'ends_at'])
            ->map(fn ($row) => [
                'start' => Carbon::parse($row->starts_at, $timezone),
                'end'   => Carbon::parse($row->ends_at, $timezone),
            ]);

        $slots = [];

        $period = CarbonPeriod::create(
            $desde->copy()->startOfDay(),
            '1 day',
            $hasta->copy()->startOfDay()
        );

        foreach ($period as $day) {
            $weekday = (int) $day->dayOfWeek; // 0 (domingo) ... 6 (sábado)
            $matches = $horarios->filter(function ($horario) use ($weekday) {
                $value = (int) $horario->dia_semana;
                $normalized = $weekday === 0 ? 7 : $weekday; // soporta 1-7
                return $value === $weekday || $value === $normalized;
            });

            foreach ($matches as $horario) {
                $slotMinutes = max(5, (int) ($horario->slot_min ?? 30));

                $inicio = Carbon::parse(
                    $day->format('Y-m-d') . ' ' . $horario->hora_inicio,
                    $timezone
                );
                $fin = Carbon::parse(
                    $day->format('Y-m-d') . ' ' . $horario->hora_fin,
                    $timezone
                );

                for ($cursor = $inicio->copy(); $cursor->lt($fin); $cursor->addMinutes($slotMinutes)) {
                    $cursorEnd = $cursor->copy()->addMinutes($slotMinutes);
                    if ($cursorEnd->gt($fin)) {
                        break;
                    }

                    $taken = false;
                    foreach ($citas as $cita) {
                        if ($cursor->lt($cita['end']) && $cursorEnd->gt($cita['start'])) {
                            $taken = true;
                            break;
                        }
                    }

                    if (!$taken && $cursor->lessThanOrEqualTo($now)) {
                        $taken = true;
                    }

                    $slots[] = [
                        'start' => $cursor->toIso8601String(),
                        'end'   => $cursorEnd->toIso8601String(),
                        'taken' => $taken,
                    ];
                }
            }
        }

        return response()->json($slots);
    }
}