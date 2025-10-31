<?php

namespace App\Http\Controllers\Api\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Cita, Medico};
use Carbon\Carbon;

class CitasController extends Controller
{
    public function index(Request $req)
    {
        $user   = $req->user();
        $medico = Medico::where('user_id', $user->id)->firstOrFail();

        $date = $req->query('date', now()->toDateString());
        $day  = Carbon::parse($date)->toDateString();

        $rows = Cita::with(['paciente.user'])
            ->where('medico_id', $medico->id)
            ->whereDate('starts_at', $day)
            ->orderBy('starts_at')
            ->get()
            ->map(function(Cita $c){
                return [
                    'id'        => $c->id,
                    'starts_at' => $c->starts_at?->toIso8601String(),
                    'paciente'  => $c->paciente?->user?->name ?? 'Paciente',
                    'estado'    => $c->estado ?: 'pendiente',
                ];
            });

        return response()->json($rows);
    }

    public function confirmar(Request $req, int $id)
    {
        $cita = $this->citaDelMedico($req, $id);
        $cita->update(['estado' => 'confirmada']);
        return response()->json(['ok' => true]);
    }

    public function completar(Request $req, int $id)
    {
        $cita = $this->citaDelMedico($req, $id);
        $cita->update(['estado' => 'completada']);
        return response()->json(['ok' => true]);
    }

    public function cancelar(Request $req, int $id)
    {
        $cita = $this->citaDelMedico($req, $id);
        $cita->update([
            'estado'        => 'cancelada',
            'canceled_by_user_id' => $req->user()->id,
            'cancel_reason' => $req->input('reason')
        ]);
        return response()->json(['ok' => true]);
    }

    private function citaDelMedico(Request $req, int $id): Cita
    {
        $medico = Medico::where('user_id', $req->user()->id)->firstOrFail();
        return Cita::where('medico_id', $medico->id)->where('id', $id)->firstOrFail();
    }
}
