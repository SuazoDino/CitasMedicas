<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Handle a forgot password request and send reset link instructions.
     */
    public function sendLink(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $status = Password::sendResetLink($credentials);

            if ($status !== Password::RESET_LINK_SENT) {
                Log::notice('Password reset link request did not complete successfully.', [
                    'email' => $credentials['email'],
                    'status' => $status,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Password reset link request failed.', [
                'email' => $credentials['email'],
                'exception' => $e,
            ]);
        }
        return response()->json([
            'message' => __('Si el correo existe en nuestros registros, enviaremos un enlace para restablecer la contraseña.'),
        ]);
    }

    /**
     * Reset the password with the provided token.
     */
    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $status = Password::reset($data, function ($user) use ($data) {
            $user->forceFill([
                'password' => Hash::make($data['password']),
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __('Tu contraseña se restableció correctamente. Ya puedes iniciar sesión.'),
            ]);
        }

        return response()->json([
            'message' => __('El token de restablecimiento no es válido o ya fue utilizado.'),
        ], 422);
    }
}