<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use Illuminate\Http\Request;

class EspecialidadController extends Controller
{
    public function index()
    {
        return response()->json(Especialidad::orderBy('nombre_especialidad')->paginate(15));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_especialidad' => 'required|string|max:100|unique:especialidades,nombre_especialidad',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $especialidad = Especialidad::create($request->only(['nombre_especialidad', 'descripcion']));

        return response()->json(['message' => 'Especialidad creada', 'especialidad' => $especialidad], 201);
    }

    public function show($id)
    {
        return response()->json(Especialidad::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $especialidad = Especialidad::findOrFail($id);

        $request->validate([
            'nombre_especialidad' => 'sometimes|string|max:100|unique:especialidades,nombre_especialidad,' . $id,
            'descripcion' => 'nullable|string|max:255',
        ]);

        $especialidad->update($request->only(['nombre_especialidad', 'descripcion']));

        return response()->json(['message' => 'Especialidad actualizada', 'especialidad' => $especialidad]);
    }

    public function destroy($id)
    {
        Especialidad::findOrFail($id)->delete();
        return response()->json(['message' => 'Especialidad eliminada']);
    }
}
