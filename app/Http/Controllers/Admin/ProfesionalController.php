<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfesionalMedico;
use Illuminate\Http\Request;

class ProfesionalController extends Controller
{
    public function index(Request $request)
    {
        $query = ProfesionalMedico::with(['usuario:id,email,estado', 'especialidad:id,nombre_especialidad']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nombres', 'like', "%$s%")
                  ->orWhere('apellidos', 'like', "%$s%")
                  ->orWhere('numero_colegiatura', 'like', "%$s%");
            });
        }
        if ($request->filled('estado_validacion')) {
            $query->where('estado_validacion', $request->estado_validacion);
        }

        return response()->json($query->orderByDesc('created_at')->paginate(15));
    }

    public function show($id)
    {
        return response()->json(
            ProfesionalMedico::with(['usuario:id,email,estado', 'especialidad'])->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $profesional = ProfesionalMedico::findOrFail($id);

        $request->validate([
            'nombres' => 'sometimes|string|max:100',
            'apellidos' => 'sometimes|string|max:100',
            'especialidad_id' => 'sometimes|exists:especialidades,id',
            'numero_colegiatura' => 'sometimes|string|max:50|unique:profesionales_medicos,numero_colegiatura,' . $id,
            'telefono' => 'nullable|string|max:20',
            'biografia' => 'nullable|string',
            'estado_validacion' => 'sometimes|in:pendiente,aprobado,rechazado',
        ]);

        $profesional->update($request->only([
            'nombres', 'apellidos', 'especialidad_id', 'numero_colegiatura',
            'telefono', 'biografia', 'estado_validacion'
        ]));

        return response()->json(['message' => 'Profesional actualizado', 'profesional' => $profesional]);
    }

    public function destroy($id)
    {
        $profesional = ProfesionalMedico::findOrFail($id);
        if ($profesional->usuario) {
            $profesional->usuario->delete();
        }
        $profesional->delete();

        return response()->json(['message' => 'Profesional eliminado']);
    }
}
