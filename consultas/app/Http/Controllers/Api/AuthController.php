<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\User;
use App\Models\Role;
use App\Models\Medico;
use App\Models\Paciente;

class AuthController extends Controller
{
    /* POST /api/auth/register/paciente */
    public function registerPaciente(Request $r)
    {
        $data = $r->validate([
            'full_name' => ['required_without:name','string','max:120'],
            'name'      => ['required_without:full_name','string','max:120'],
            'email'     => ['required','email','max:190','unique:users,email'],
            'phone'     => ['nullable','string','max:30'],
            'password'  => ['required','string','min:6'],
            'role'      => ['nullable', Rule::in(['paciente'])],

            // si tu formulario ya envía estos campos, se guardan; si no, quedan null
            'doc_tipo'   => ['nullable','string','max:10'],
            'doc_numero' => ['nullable','string','max:32'],
            'birthdate'  => ['nullable','date'],
            'gender'     => ['nullable','string','max:10'],
        ]);

        $name = $data['full_name'] ?? $data['name'];
        $phone = $data['phone'] ?? null;
        $requestedRole = $data['role'] ?? 'paciente';

        $roleId = Role::where('name', $requestedRole)->value('id');
        if (!$roleId) {
            return response()->json([
                'message' => 'No pudimos asignar el rol solicitado.',
                'errors'  => ['role' => ['El rol solicitado no existe.']],
            ], 422);
        }

        $res = DB::transaction(function () use ($data, $name, $phone, $roleId) {
            $u = User::create([
                'name'     => $name,
                'email'    => $data['email'],
                'phone'    => $phone,
                'password' => Hash::make($data['password']),
            ]);

            $u->roles()->syncWithoutDetaching([$roleId]);

            // fila en 'pacientes' (si ya existe, no duplica)
            Paciente::firstOrCreate(
                ['user_id' => $u->id],
                [
                    'doc_tipo'   => $data['doc_tipo']   ?? null,
                    'doc_numero' => $data['doc_numero'] ?? null,
                    'birthdate'  => $data['birthdate']  ?? null,
                    'gender'     => $data['gender']     ?? null,
                ]
            );

            $token = $u->createToken('web')->plainTextToken;

            // devolvemos también los roles para que el front pueda redirigir sin llamar a /me
            return response()->json([
                'token' => $token,
                'user'  => $u,
                'roles' => $u->roles()->pluck('name'),
            ], 201);
        });

        return $res;
    }

    /* POST /api/auth/register/medico */
    public function registerMedico(Request $r)
    {
        $data = $r->validate([
            'full_name'     => ['required_without:name','string','max:120'],
            'name'          => ['required_without:full_name','string','max:120'],
            'email'         => ['required','email','max:190','unique:users,email'],
            'phone'         => ['nullable','string','max:30'],
            'password'      => ['required','string','min:6'],
            'role'          => ['nullable', Rule::in(['medico'])],

            // columnas que tienes en la tabla 'medicos'
            'id_doc_tipo'   => ['required','string','max:10'],
            'id_doc_numero' => ['required','string','max:32'],
            'lic_tipo'      => ['required','string','max:16'],
            'lic_numero'    => ['required','string','max:32'],
            'lic_pais'      => ['required','string','max:4'],
        ]);

        $name = $data['full_name'] ?? $data['name'];
        $phone = $data['phone'] ?? null;
        $requestedRole = $data['role'] ?? 'medico';

        $roleId = Role::where('name', $requestedRole)->value('id');
        if (!$roleId) {
            return response()->json([
                'message' => 'No pudimos asignar el rol solicitado.',
                'errors'  => ['role' => ['El rol solicitado no existe.']],
            ], 422);
        }

        $res = DB::transaction(function () use ($data, $name, $phone, $roleId) {
            $u = User::create([
                'name'     => $name,
                'email'    => $data['email'],
                'phone'    => $phone,
                'password' => Hash::make($data['password']),
            ]);

            // rol médico
            $u->roles()->syncWithoutDetaching([$roleId]);

            // fila en 'medicos'
            Medico::create([
                'user_id'        => $u->id,
                'id_doc_tipo'    => $data['id_doc_tipo'],
                'id_doc_numero'  => $data['id_doc_numero'],
                'lic_tipo'       => $data['lic_tipo'],
                'lic_numero'     => $data['lic_numero'],
                'lic_pais'       => $data['lic_pais'],
                'verif_status'   => 'provisional',   // ✅ valor válido según tu ENUM
                'is_searchable'  => 1,               // por si no tiene default
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $token = $u->createToken('web')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user'  => $u,
                'roles' => $u->roles()->pluck('name'),
            ], 201);
        });

        return $res;
    }

    /* POST /api/auth/login */
    public function login(Request $r)
    {
        $data = $r->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
            
        ]);

        $u = User::where('email', $data['email'])->first();
        if (!$u || !Hash::check($data['password'], $u->password)) {
            return response()->json([
                'message' => 'Las credenciales proporcionadas no son válidas.',
                'errors'  => [
                    'email' => ['Revisa tu correo y contraseña e inténtalo nuevamente.'],
                ],
                'hints'   => [
                    'reset' => 'Si olvidaste tu contraseña puedes solicitar un enlace de recuperación.',
                ],
            ], 422);
        }

        $token = $u->createToken('web')->plainTextToken;
        return [
            'token' => $token,
            'user'  => $u,
            'roles' => $u->roles()->pluck('name'),
        ];
    }

    /* GET /api/auth/me */
    public function me(Request $r)
    {
        $u = $r->user();
        $roles = $u->roles()->pluck('name');
        return ['user' => $u, 'roles' => $roles];
    }

    /* POST /api/auth/logout */
    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()->delete();
        return response()->noContent();
    }
}



