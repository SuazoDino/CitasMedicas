<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    private function pacienteOrFail(Request $request)
    {
        $paciente = $request->user()->paciente;
        abort_if(!$paciente, 403, 'Solo los pacientes pueden realizar esta acción.');
        return $paciente;
    }

    public function index(Request $request)
    {
        $paciente = $this->pacienteOrFail($request);

        $query = Cita::with(['medico.usuario:id,email', 'medico.especialidades:id,nombre', 'especialidad:id,nombre'])
            ->where('paciente_id', $paciente->id);

        if ($request->get('periodo') === 'pasadas') {
            $query->where(function ($q) {
                $q->where('starts_at', '<', now())->orWhere('estado', 'completada');
            });
        } else {
            $query->where('starts_at', '>=', now())->whereNotIn('estado', ['cancelada', 'completada']);
        }

        return response()->json($query->orderBy('starts_at')->get());
    }

    public function store(Request $request)
    {
        $paciente = $this->pacienteOrFail($request);

        $data = $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'especialidad_id' => 'required|exists:especialidades,id',
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'required|date|after:starts_at',
            'motivo' => 'nullable|string|max:140',
        ]);

        $conflict = Cita::where('medico_id', $data['medico_id'])
            ->where('starts_at', $data['starts_at'])
            ->where('estado', '!=', 'cancelada')
            ->exists();

        if ($conflict) {
            return response()->json(['message' => 'Ese horario ya no está disponible, elige otro.'], 422);
        }

        $cita = Cita::create([
            ...$data,
            'paciente_id' => $paciente->id,
            'estado' => 'pendiente',
            'created_by_user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Cita agendada exitosamente',
            'cita' => $cita->load(['medico.usuario', 'especialidad']),
        ], 201);
    }

    public function cancelar(Request $request, $id)
    {
        $paciente = $this->pacienteOrFail($request);

        $cita = Cita::where('paciente_id', $paciente->id)->findOrFail($id);

        if (in_array($cita->estado, ['completada', 'cancelada'])) {
            return response()->json(['message' => 'Esta cita ya no se puede cancelar.'], 422);
        }

        $data = $request->validate(['cancel_reason' => 'nullable|string|max:180']);

        $cita->update([
            'estado' => 'cancelada',
            'canceled_by_user_id' => $request->user()->id,
            'cancel_reason' => $data['cancel_reason'] ?? 'Cancelado por el paciente',
        ]);

        return response()->json(['message' => 'Cita cancelada']);
    }
}
