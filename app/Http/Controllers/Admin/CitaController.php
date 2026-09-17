<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Paciente;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function lookups()
    {
        $medicos = Medico::with(['usuario:id,email', 'especialidades:id,nombre'])
            ->get(['id', 'user_id'])
            ->map(fn ($medico) => [
                'id' => $medico->id,
                'label' => trim(
                    ($medico->usuario->email ?? "Médico #{$medico->id}")
                    . ($medico->especialidades->isNotEmpty() ? ' — ' . $medico->especialidades->pluck('nombre')->implode(', ') : '')
                ),
            ]);

        $pacientes = Paciente::with('usuario:id,email')
            ->get(['id', 'user_id'])
            ->map(fn ($paciente) => [
                'id' => $paciente->id,
                'label' => $paciente->usuario->email ?? "Paciente #{$paciente->id}",
            ]);

        $especialidades = Especialidad::orderBy('nombre')->get(['id', 'nombre']);

        return response()->json([
            'medicos' => $medicos,
            'pacientes' => $pacientes,
            'especialidades' => $especialidades,
        ]);
    }

    public function index(Request $request)
    {
        $query = Cita::with(['medico.usuario:id,email', 'paciente.usuario:id,email', 'especialidad:id,nombre']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('medico_id')) {
            $query->where('medico_id', $request->medico_id);
        }
        if ($request->filled('desde')) {
            $query->where('starts_at', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->where('starts_at', '<=', $request->hasta);
        }
        if ($request->filled('search')) {
            $query->where('motivo', 'like', '%' . $request->search . '%');
        }

        $citas = $query->orderByDesc('starts_at')->paginate(15);

        return response()->json($citas);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'medico_id' => 'required|exists:medicos,id',
            'paciente_id' => 'required|exists:pacientes,id',
            'especialidad_id' => 'required|exists:especialidades,id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'motivo' => 'nullable|string|max:140',
            'notas' => 'nullable|string',
            'estado' => 'sometimes|in:pendiente,confirmada,cancelada,completada,no_asistio',
        ]);

        $conflict = Cita::where('medico_id', $data['medico_id'])
            ->where('starts_at', $data['starts_at'])
            ->exists();

        if ($conflict) {
            return response()->json(['message' => 'El médico ya tiene una cita agendada en ese horario.'], 422);
        }

        $cita = Cita::create([
            ...$data,
            'estado' => $data['estado'] ?? 'pendiente',
            'created_by_user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Cita creada exitosamente',
            'cita' => $cita->load(['medico.usuario', 'paciente.usuario', 'especialidad']),
        ], 201);
    }

    public function show($id)
    {
        $cita = Cita::with(['medico.usuario', 'paciente.usuario', 'especialidad', 'creadoPor', 'canceladoPor'])->findOrFail($id);
        return response()->json($cita);
    }

    public function update(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);

        $data = $request->validate([
            'medico_id' => 'sometimes|exists:medicos,id',
            'paciente_id' => 'sometimes|exists:pacientes,id',
            'especialidad_id' => 'sometimes|exists:especialidades,id',
            'starts_at' => 'sometimes|date',
            'ends_at' => 'sometimes|date|after:starts_at',
            'motivo' => 'nullable|string|max:140',
            'notas' => 'nullable|string',
            'estado' => 'sometimes|in:pendiente,confirmada,cancelada,completada,no_asistio',
            'cancel_reason' => 'nullable|string|max:180',
        ]);

        $medicoId = $data['medico_id'] ?? $cita->medico_id;
        $startsAt = $data['starts_at'] ?? $cita->starts_at;

        $conflict = Cita::where('medico_id', $medicoId)
            ->where('starts_at', $startsAt)
            ->where('id', '!=', $cita->id)
            ->exists();

        if ($conflict) {
            return response()->json(['message' => 'El médico ya tiene una cita agendada en ese horario.'], 422);
        }

        if (($data['estado'] ?? null) === 'cancelada') {
            $data['canceled_by_user_id'] = $request->user()->id;
        }

        $cita->update($data);

        return response()->json([
            'message' => 'Cita actualizada',
            'cita' => $cita->load(['medico.usuario', 'paciente.usuario', 'especialidad']),
        ]);
    }

    public function destroy($id)
    {
        Cita::findOrFail($id)->delete();
        return response()->json(['message' => 'Cita eliminada']);
    }
}
