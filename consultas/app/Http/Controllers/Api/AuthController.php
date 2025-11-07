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
    private const GENDER_ALIASES = [
        'm' => 'M',
        'f' => 'F',
        'x' => 'X',
        'M' => 'M',
        'F' => 'F',
        'X' => 'X',
        'masculino' => 'M',
        'femenino' => 'F',
        'no-binario' => 'X',
        'no binario' => 'X',
        'no_binario' => 'X',
        'prefiero-no-decirlo' => null,
        'prefiero no decirlo' => null,
        'prefiero_no_decirlo' => null,
    ];
    /* POST /api/auth/register/paciente */
    public function registerPaciente(Request $r)
    {
        if ($r->has('birthdate')) {
            $rawBirthdate = $r->input('birthdate');
            $trimmedBirthdate = is_string($rawBirthdate) ? trim($rawBirthdate) : $rawBirthdate;

            if ($trimmedBirthdate === '' || $trimmedBirthdate === null) {
                $r->merge(['birthdate' => null]);
            } else {
                $normalizedBirthdate = $this->normalizeBirthdate($rawBirthdate);
                if ($normalizedBirthdate !== null) {
                    $r->merge(['birthdate' => $normalizedBirthdate]);
                }
            }
        }
        $genderOptions = array_keys(self::GENDER_ALIASES);
        $genderVariants = [];
        foreach ($genderOptions as $value) {
            $lower = strtolower($value);
            $genderVariants[] = $lower;
            $genderVariants[] = strtoupper($lower);
            $genderVariants[] = ucfirst($lower);
        }
        $genderOptions = array_values(array_unique(array_merge($genderOptions, $genderVariants)));
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
            'gender'     => ['nullable','string','max:32', Rule::in($genderOptions)],
        ]);

        if (array_key_exists('gender', $data)) {
            $data['gender'] = $this->normalizeGender($data['gender']);
        }
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
            \Log::info('Creando usuario paciente', [
                'email' => $data['email'],
                'name' => $name,
                'password_length' => strlen($data['password']),
            ]);
            
            // El modelo User tiene 'password' => 'hashed' en el cast, así que Laravel lo hashea automáticamente
            $u = User::create([
                'name'     => $name,
                'email'    => $data['email'],
                'phone'    => $phone,
                'password' => $data['password'], // Laravel lo hashea automáticamente por el cast
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
            \Log::info('Creando usuario médico', [
                'email' => $data['email'],
                'name' => $name,
                'password_length' => strlen($data['password']),
            ]);
            
            // El modelo User tiene 'password' => 'hashed' en el cast, así que Laravel lo hashea automáticamente
            $u = User::create([
                'name'     => $name,
                'email'    => $data['email'],
                'phone'    => $phone,
                'password' => $data['password'], // Laravel lo hashea automáticamente por el cast
            ]);

            // rol médico
            $u->roles()->syncWithoutDetaching([$roleId]);

            // fila en 'medicos'
            $medico = Medico::create([
                'user_id'        => $u->id,
                'id_doc_tipo'    => $data['id_doc_tipo'],
                'id_doc_numero'  => $data['id_doc_numero'],
                'lic_tipo'       => $data['lic_tipo'],
                'lic_numero'     => $data['lic_numero'],
                'lic_pais'       => $data['lic_pais'],
                'verif_status'   => 'provisional',
                'is_searchable'  => true,
            ]);
            
            \Log::info('Usuario médico creado exitosamente', [
                'user_id' => $u->id,
                'medico_id' => $medico->id,
                'roles' => $u->roles()->pluck('name')->toArray(),
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
        
        if (!$u) {
            \Log::warning('Login fallido: usuario no encontrado', ['email' => $data['email']]);
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
        
        // Obtener el hash directamente de la BD sin pasar por el cast de Eloquent
        $storedPassword = DB::table('users')->where('id', $u->id)->value('password');
        
        \Log::info('Verificando contraseña', [
            'email' => $data['email'],
            'user_id' => $u->id,
            'password_length' => strlen($data['password']),
            'stored_hash_length' => strlen($storedPassword),
            'stored_hash_preview' => substr($storedPassword, 0, 20) . '...',
        ]);
        
        $passwordMatch = Hash::check($data['password'], $storedPassword);
        
        // Si no coincide, puede ser que la contraseña esté doblemente hasheada
        // Intentar corregirla automáticamente re-asignando la contraseña en texto plano
        if (!$passwordMatch) {
            \Log::warning('Contraseña no coincide, intentando corregir posible doble hash', [
                'email' => $data['email'],
                'user_id' => $u->id,
            ]);
            
            try {
                // Guardar el hash original por si acaso
                $originalHash = $storedPassword;
                
                // Re-asignar la contraseña en texto plano
                // El cast 'hashed' la hasheará correctamente una sola vez
                $u->password = $data['password'];
                $u->save();
                
                // Verificar si ahora funciona
                $u->refresh();
                $newStoredPassword = DB::table('users')->where('id', $u->id)->value('password');
                $newMatch = Hash::check($data['password'], $newStoredPassword);
                
                if ($newMatch) {
                    \Log::info('Contraseña corregida automáticamente (estaba doblemente hasheada)', [
                        'email' => $data['email'],
                        'user_id' => $u->id,
                    ]);
                    $passwordMatch = true;
                } else {
                    // Si no funciona, restaurar el hash original
                    DB::table('users')->where('id', $u->id)->update(['password' => $originalHash]);
                    \Log::warning('No se pudo corregir la contraseña, restaurando hash original', [
                        'email' => $data['email'],
                        'user_id' => $u->id,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Error al intentar corregir contraseña', [
                    'email' => $data['email'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
        if (!$passwordMatch) {
            \Log::warning('Login fallido: contraseña incorrecta', [
                'email' => $data['email'],
                'user_id' => $u->id,
                'password_length' => strlen($data['password']),
                'stored_hash_length' => strlen($storedPassword),
            ]);
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
        $roles = $u->roles()->pluck('name');
        
        \Log::info('Login exitoso', [
            'user_id' => $u->id,
            'email' => $u->email,
            'roles' => $roles->toArray(),
        ]);
        
        return response()->json([
            'token' => $token,
            'user'  => $u,
            'roles' => $roles,
        ]);
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
    private function normalizeGender(?string $gender): ?string
    {
        if ($gender === null) {
            return null;
        }

        $trimmed = trim($gender);
        if ($trimmed === '') {
            return null;
        }

        if (array_key_exists($trimmed, self::GENDER_ALIASES)) {
            return self::GENDER_ALIASES[$trimmed];
        }

        $lower = strtolower($trimmed);
        if (array_key_exists($lower, self::GENDER_ALIASES)) {
            return self::GENDER_ALIASES[$lower];
        }

        return null;
    }

    private function normalizeBirthdate($birthdate): ?string
    {
        if ($birthdate === null) {
            return null;
        }

        if (!is_string($birthdate)) {
            return null;
        }

        $trimmed = trim($birthdate);
        if ($trimmed === '') {
            return null;
        }

        $formats = [
            'Y-m-d', 'Y/m/d',
            'd/m/Y', 'd-m-Y', 'd.m.Y',
            'j/n/Y', 'j-n-Y', 'j.n.Y',
        ];

        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $trimmed);

            if (!($dt instanceof \DateTime)) {
                continue;
            }

            $errors = \DateTime::getLastErrors();
            $hasIssues = is_array($errors)
                ? (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0)
                : false;

            if (!$hasIssues) {
                return $dt->format('Y-m-d');
            }
        }

        return null;
    }
}



