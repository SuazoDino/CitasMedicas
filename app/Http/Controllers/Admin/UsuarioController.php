<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('rol_id')) {
            $query->where('rol_id', $request->rol_id);
        }

        $usuarios = $query->orderByDesc('created_at')->paginate(15);

        return response()->json($usuarios);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'rol_id' => 'required|exists:roles,id',
            'estado' => 'sometimes|in:activo,inactivo,suspendido',
        ]);

        $usuario = User::create([
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'rol_id' => $request->rol_id,
            'estado' => $request->estado ?? 'activo',
        ]);

        // Un usuario creado por el admin ya está avalado por él: no necesita
        // confirmar su correo para poder iniciar sesión.
        $usuario->markEmailAsVerified();

        return response()->json(['message' => 'Usuario creado exitosamente', 'usuario' => $usuario], 201);
    }

    public function show($id)
    {
        $usuario = User::with('roles')->findOrFail($id);
        return response()->json($usuario);
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $request->validate([
            'estado' => 'sometimes|in:activo,inactivo,suspendido',
            'rol_id' => 'sometimes|exists:roles,id',
        ]);

        $usuario->update($request->only(['estado', 'rol_id']));

        return response()->json(['message' => 'Usuario actualizado', 'usuario' => $usuario]);
    }

    public function destroy($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado']);
    }
}
