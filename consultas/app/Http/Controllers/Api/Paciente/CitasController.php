<?php

namespace App\Http\Controllers\Api\Paciente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Cita, Paciente};
use Illuminate\Support\Str;

class CitasController extends Controller
{
    // /api/paciente/citas/proximas
    public function proximas(Request $req)
    {
        $pac = Paciente::where('user_id', $req->user()->id)->firstOrFail();

        $rows = Cita::with(['medico.user','especialidad'])
            ->where('paciente_id', $pac->id)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(10)
            ->get()
            ->map(function(Cita $c){
                return [
                    'id'           => $c->id,
                    'medico'       => $c->medico?->user?->name ?? 'Médico',
                    'especialidad' => $c->especialidad?->nombre ?? null,
                    'fecha'        => $c->starts_at?->timezone(config('app.timezone'))->format('d/m/Y'),
                    'hora'         => $c->starts_at?->timezone(config('app.timezone'))->format('H:i'),
                    'lugar'        => null, // si luego agregas consultorio/sede, mapea aquí
                    'estado'       => $c->estado ?: 'pendiente',
                ];
            });

        return response()->json($rows);
    }
}
