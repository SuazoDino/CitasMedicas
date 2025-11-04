<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NotificationPreferenceController extends Controller
{
    public function updatePaciente(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $pacienteId = DB::table('pacientes')->where('user_id', $userId)->value('id');

        if (!$pacienteId) {
            return response()->json(['message' => 'No es paciente'], 403);
        }

        return $this->persistPreferences($request, $userId, 'paciente');
    }

    public function updateMedico(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $medicoId = DB::table('medicos')->where('user_id', $userId)->value('id');

        if (!$medicoId) {
            return response()->json(['message' => 'No es médico'], 403);
        }

        return $this->persistPreferences($request, $userId, 'medico');
    }

    protected function persistPreferences(Request $request, int $userId, string $context): JsonResponse
    {
        $data = $request->validate([
            'email_opt_in' => 'sometimes|boolean',
            'sms_opt_in' => 'sometimes|boolean',
            'sms_number' => 'nullable|string|max:30',
            'push_opt_in' => 'sometimes|boolean',
            'push_token' => 'nullable|string|max:255',
            'push_platform' => 'nullable|string|max:50',
            'push_metadata' => 'nullable|array',
        ]);

        /** @var NotificationPreference $preference */
        $preference = NotificationPreference::firstOrNew(['user_id' => $userId]);

        if (!$preference->exists) {
            $preference->email_opt_in = true;
        }

        if (array_key_exists('email_opt_in', $data)) {
            $preference->email_opt_in = (bool) $data['email_opt_in'];
        }

        if (array_key_exists('sms_opt_in', $data)) {
            $now = Carbon::now();
            $smsOptIn = (bool) $data['sms_opt_in'];

            $candidateNumber = $data['sms_number'] ?? $preference->sms_number ?? $request->user()->phone;

            if ($smsOptIn && empty($candidateNumber)) {
                throw ValidationException::withMessages([
                    'sms_number' => __('Debe registrar un número de teléfono para activar SMS.'),
                ]);
            }

            $preference->sms_opt_in = $smsOptIn;
            $preference->sms_number = $candidateNumber;

            if ($smsOptIn && !$preference->sms_opted_in_at) {
                $preference->sms_opted_in_at = $now;
            }

            if (!$smsOptIn) {
                $preference->sms_opted_in_at = null;
            }
        }

        if (array_key_exists('push_opt_in', $data)) {
            $now = Carbon::now();
            $pushOptIn = (bool) $data['push_opt_in'];

            $candidateToken = $data['push_token'] ?? $preference->push_token;

            if ($pushOptIn && empty($candidateToken)) {
                throw ValidationException::withMessages([
                    'push_token' => __('Se requiere un token de dispositivo para activar notificaciones push.'),
                ]);
            }

            $preference->push_opt_in = $pushOptIn;
            $preference->push_token = $candidateToken;
            $preference->push_platform = $data['push_platform'] ?? $preference->push_platform;
            $preference->push_metadata = $data['push_metadata'] ?? $preference->push_metadata;

            if ($pushOptIn && !$preference->push_opted_in_at) {
                $preference->push_opted_in_at = $now;
            }

            if (!$pushOptIn) {
                $preference->push_opted_in_at = null;
                if (empty($data['push_token'])) {
                    $preference->push_token = null;
                }
            }
        }

        if (array_key_exists('push_platform', $data) && !array_key_exists('push_opt_in', $data)) {
            $preference->push_platform = $data['push_platform'];
        }

        if (array_key_exists('push_metadata', $data) && !array_key_exists('push_opt_in', $data)) {
            $preference->push_metadata = $data['push_metadata'];
        }

        $preference->save();

        return response()->json([
            'contexto' => $context,
            'email_opt_in' => $preference->email_opt_in,
            'sms_opt_in' => $preference->sms_opt_in,
            'sms_number' => $preference->sms_number,
            'push_opt_in' => $preference->push_opt_in,
            'push_token' => $preference->push_token,
            'push_platform' => $preference->push_platform,
            'push_metadata' => $preference->push_metadata,
        ]);
    }
}