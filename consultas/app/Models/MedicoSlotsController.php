<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MedicoSlotsController extends Controller
{
    // GET /api/medicos/{medico}/slots?fecha=YYYY-MM-DD
    public function slots(Request $r, $medicoId)
    {
        $r->validate(['fecha' => 'required|date']);
        $fecha = Carbon::parse($r->query('fecha'), 'America/Lima')->startOfDay();

        // 1) Horarios del día
        $dow0 = (int)$fecha->dayOfWeek;           // 0=Dom ... 6=Sáb
        $dow1 = $dow0 === 0 ? 7 : $dow0;          // 1=Lun ... 7=Dom

        $horarios = DB::table('medico_horarios')
            ->where('medico_id', $medicoId)
            ->where('activo', 1)
            ->whereIn('dia_semana', [$dow0, $dow1])   // soporta 0-6 o 1-7
            ->get(['hora_inicio','hora_fin','slot_min']);

        if ($horarios->isEmpty()) return response()->json(['slots' => []]);

        // 2) Citas ya tomadas ese día
        $citas = DB::table('citas')
            ->where('medico_id', $medicoId)
            ->whereDate('starts_at', $fecha->toDateString())
            ->whereIn('estado', ['pendiente','confirmada'])
            ->get(['starts_at','ends_at'])
            ->map(fn($c)=>[
                'start'=>Carbon::parse($c->starts_at,'America/Lima'),
                'end'  =>Carbon::parse($c->ends_at,'America/Lima')
            ]);

        // 3) Generar slots libres
        $ahora = Carbon::now('America/Lima')->addMinutes(5); // margen
        $slots = [];

        foreach ($horarios as $h) {
            $slotMin = (int)($h->slot_min ?? 30);
            $inicio  = Carbon::parse($fecha->toDateString().' '.$h->hora_inicio, 'America/Lima');
            $fin     = Carbon::parse($fecha->toDateString().' '.$h->hora_fin,    'America/Lima');

            for ($t = $inicio->copy(); $t->lt($fin); $t->addMinutes($slotMin)) {
                $tEnd = $t->copy()->addMinutes($slotMin);
                if ($tEnd->gt($fin)) break;

                // No permitir pasado si es hoy
                if ($fecha->isSameDay($ahora) && $t->lte($ahora)) continue;

                // Chequeo de solape con citas existentes: startA < endB && endA > startB
                $choca = false;
                foreach ($citas as $c) {
                    if ($t->lt($c['end']) && $tEnd->gt($c['start'])) { $choca = true; break; }
                }
                if (!$choca) {
                    $slots[] = [
                        'starts_at' => $t->toIso8601String(),
                        'ends_at'   => $tEnd->toIso8601String(),
                    ];
                }
            }
        }

        return response()->json(['slots' => $slots]);
    }
}
