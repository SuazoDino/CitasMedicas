<?php

namespace App\Http\Controllers\Api\Medico;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HorariosController extends Controller
{
    private function medicoId(int $userId): ?int
    {
        return DB::table('medicos')->where('user_id', $userId)->value('id');
    }

    private function normalizeDay($value): int
    {
        $day = (int) $value;
        if ($day === 7) {
            $day = 0;
        }
        if ($day < 0 || $day > 6) {
            throw ValidationException::withMessages([
                'dia_semana' => 'El día de la semana debe estar entre 0 (domingo) y 6 (sábado).',
            ]);
        }
        return $day;
    }

    private function normalizeTime(string $value, string $field): string
    {
        $formats = ['H:i', 'H:i:s'];
        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $value);
                if ($parsed !== false) {
                    return $parsed->format('H:i:s');
                }
            } catch (\Exception $e) {
                // continue
            }
        }

        throw ValidationException::withMessages([
            $field => 'Formato de hora inválido.',
        ]);
    }

    private function buildPayload(array $input, array $fallback = []): array
    {
        $dia = $input['dia_semana'] ?? $fallback['dia_semana'] ?? null;
        $horaInicio = $input['hora_inicio'] ?? $fallback['hora_inicio'] ?? null;
        $horaFin = $input['hora_fin'] ?? $fallback['hora_fin'] ?? null;
        if ($dia === null || $horaInicio === null || $horaFin === null) {
            throw ValidationException::withMessages([
                'horario' => 'Los campos día, hora de inicio y hora de fin son obligatorios.',
            ]);
        }

        $dia = $this->normalizeDay($dia);
        $inicio = $this->normalizeTime($horaInicio, 'hora_inicio');
        $fin = $this->normalizeTime($horaFin, 'hora_fin');

        if ($inicio >= $fin) {
            throw ValidationException::withMessages([
                'hora_fin' => 'La hora de fin debe ser posterior a la hora de inicio.',
            ]);
        }

        $slotMin = (int) ($input['slot_min'] ?? $fallback['slot_min'] ?? 30);
        if ($slotMin < 5 || $slotMin > 480) {
            throw ValidationException::withMessages([
                'slot_min' => 'La duración debe estar entre 5 y 480 minutos.',
            ]);
        }

        $activo = array_key_exists('activo', $input)
            ? (bool) $input['activo']
            : ($fallback['activo'] ?? true);

        return [
            'dia_semana' => $dia,
            'hora_inicio' => $inicio,
            'hora_fin' => $fin,
            'slot_min' => $slotMin,
            'activo' => $activo,
        ];
    }

    private function hasOverlap(int $medicoId, array $payload, ?int $ignoreId = null): bool
    {
        if (!$payload['activo']) {
            return false;
        }

        return DB::table('medico_horarios')
            ->where('medico_id', $medicoId)
            ->where('dia_semana', $payload['dia_semana'])
            ->where('activo', true)
            ->when($ignoreId, fn ($q) => $q->where('id', '<>', $ignoreId))
            ->where(function ($q) use ($payload) {
                $q->where('hora_inicio', '<', $payload['hora_fin'])
                  ->where('hora_fin', '>', $payload['hora_inicio']);
            })
            ->exists();
    }

    private function formatRow(object $row): array
    {
        return [
            'id' => $row->id,
            'medico_id' => $row->medico_id,
            'dia_semana' => (int) $row->dia_semana,
            'hora_inicio' => substr($row->hora_inicio, 0, 5),
            'hora_fin' => substr($row->hora_fin, 0, 5),
            'slot_min' => (int) $row->slot_min,
            'activo' => (bool) $row->activo,
        ];
    }

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $medicoId = $this->medicoId($user->id);
        if (!$medicoId) {
            return response()->json(['medico_id' => null, 'horarios' => []]);
        }

        $rows = DB::table('medico_horarios')
            ->where('medico_id', $medicoId)
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        return response()->json([
            'medico_id' => $medicoId,
            'horarios' => $rows->map(fn ($row) => $this->formatRow($row))->values(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $medicoId = $this->medicoId($user->id);
        if (!$medicoId) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $validated = $request->validate([
            'dia_semana' => ['required', 'integer'],
            'hora_inicio' => ['required', 'string'],
            'hora_fin' => ['required', 'string'],
            'slot_min' => ['nullable', 'integer'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $payload = $this->buildPayload($validated);

        if ($this->hasOverlap($medicoId, $payload)) {
            return response()->json([
                'message' => 'Esta franja se superpone con otra franja activa.',
            ], 422);
        }

        $id = DB::table('medico_horarios')->insertGetId([
            'medico_id' => $medicoId,
            'dia_semana' => $payload['dia_semana'],
            'hora_inicio' => $payload['hora_inicio'],
            'hora_fin' => $payload['hora_fin'],
            'slot_min' => $payload['slot_min'],
            'activo' => $payload['activo'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('medico_horarios')->where('id', $id)->first();

        return response()->json([
            'medico_id' => $medicoId,
            'horario' => $this->formatRow($row),
        ], 201);
    }

    public function update(Request $request, int $horario)
    {
        $horarioId = $horario;
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $medicoId = $this->medicoId($user->id);
        if (!$medicoId) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $row = DB::table('medico_horarios')
            ->where('id', $horarioId)
            ->where('medico_id', $medicoId)
            ->first();

        if (!$row) {
            return response()->json(['message' => 'Horario no encontrado.'], 404);
        }

        $validated = $request->validate([
            'dia_semana' => ['sometimes', 'integer'],
            'hora_inicio' => ['sometimes', 'string'],
            'hora_fin' => ['sometimes', 'string'],
            'slot_min' => ['sometimes', 'integer'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $payload = $this->buildPayload($validated, [
            'dia_semana' => $row->dia_semana,
            'hora_inicio' => $row->hora_inicio,
            'hora_fin' => $row->hora_fin,
            'slot_min' => $row->slot_min,
            'activo' => $row->activo,
        ]);

        if ($this->hasOverlap($medicoId, $payload, $row->id)) {
            return response()->json([
                'message' => 'Esta franja se superpone con otra franja activa.',
            ], 422);
        }

        DB::table('medico_horarios')
            ->where('id', $row->id)
            ->update([
                'dia_semana' => $payload['dia_semana'],
                'hora_inicio' => $payload['hora_inicio'],
                'hora_fin' => $payload['hora_fin'],
                'slot_min' => $payload['slot_min'],
                'activo' => $payload['activo'],
                'updated_at' => now(),
            ]);

        $updated = DB::table('medico_horarios')->where('id', $row->id)->first();

        return response()->json([
            'medico_id' => $medicoId,
            'horario' => $this->formatRow($updated),
        ]);
    }

    public function destroy(Request $request, int $horario)
    {
        $horarioId = $horario;
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $medicoId = $this->medicoId($user->id);
        if (!$medicoId) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $row = DB::table('medico_horarios')
            ->where('id', $horarioId)
            ->where('medico_id', $medicoId)
            ->first();

        if (!$row) {
            return response()->json(['message' => 'Horario no encontrado.'], 404);
        }

        DB::table('medico_horarios')
            ->where('id', $row->id)
            ->update([
                'activo' => false,
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }
}