<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Paciente::with('usuario:id,email,estado');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nombres', 'like', "%$s%")
                  ->orWhere('apellidos', 'like', "%$s%")
                  ->orWhere('dni', 'like', "%$s%");
            });
        }

        return response()->json($query->orderByDesc('created_at')->paginate(15));
    }

    public function show($id)
    {
        return response()->json(Paciente::with('usuario:id,email,estado,created_at')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $paciente = Paciente::findOrFail($id);

        $request->validate([
            'nombres' => 'sometimes|string|max:100',
            'apellidos' => 'sometimes|string|max:100',
            'dni' => 'sometimes|string|max:20|unique:pacientes,dni,' . $id,
            'fecha_nacimiento' => 'sometimes|date',
            'genero' => 'sometimes|in:masculino,femenino,otro',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
        ]);

        $paciente->update($request->only(['nombres', 'apellidos', 'dni', 'fecha_nacimiento', 'genero', 'telefono', 'direccion']));

        return response()->json(['message' => 'Paciente actualizado', 'paciente' => $paciente]);
    }

    public function destroy($id)
    {
        $paciente = Paciente::findOrFail($id);
        // Also delete associated user
        if ($paciente->usuario) {
            $paciente->usuario->delete();
        }
        $paciente->delete();

        return response()->json(['message' => 'Paciente eliminado']);
    }
}
