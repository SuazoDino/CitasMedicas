<?php

namespace App\Http\Controllers\Api\Medico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Cita, Medico};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CitasController extends Controller
{
    public function index(Request $req)
    {
        $user   = $req->user();
        $medico = Medico::where('user_id', $user->id)->firstOrFail();

        $date = $req->query('date', now()->toDateString());
        $day  = Carbon::parse($date)->toDateString();

        $rows = Cita::with(['paciente.user'])
            ->where('medico_id', $medico->id)
            ->whereDate('starts_at', $day)
            ->orderBy('starts_at')
            ->get()
            ->map(function(Cita $c){
                return [
                    'id'        => $c->id,
                    'starts_at' => $c->starts_at?->toIso8601String(),
                    'paciente'  => $c->paciente?->user?->name ?? 'Paciente',
                    'estado'    => $c->estado ?: 'pendiente',
                ];
            });

        return response()->json($rows);
    }

    public function confirmar(Request $req, int $id)
    {
        $cita = $this->citaDelMedico($req, $id);
        $cita->update(['estado' => 'confirmada']);
        return response()->json(['ok' => true]);
    }

    public function completar(Request $req, int $id)
    {
        $cita = $this->citaDelMedico($req, $id);
        $cita->update(['estado' => 'completada']);
        return response()->json(['ok' => true]);
    }

    public function cancelar(Request $req, int $id)
    {
        $cita = $this->citaDelMedico($req, $id);
        $cita->update([
            'estado'        => 'cancelada',
            'canceled_by_user_id' => $req->user()->id,
            'cancel_reason' => $req->input('reason')
        ]);
        return response()->json(['ok' => true]);
    }

    public function search(Request $req)
    {
        $user   = $req->user();
        $medico = Medico::where('user_id', $user->id)->firstOrFail();
        
        $q = trim((string) $req->input('q', ''));
        
        if (empty($q) || strlen($q) < 2) {
            return response()->json([
                'pacientes' => [],
                'citas' => [],
            ]);
        }
        
        // Sanitizar query
        $q = str_replace(['%', '_'], ['\%', '\_'], $q);
        
        // Buscar TODOS los pacientes que coincidan con la búsqueda (no solo los que tienen citas)
        $pacientes = DB::table('pacientes as p')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->where('u.name', 'like', "%{$q}%")
            ->select([
                'p.id as paciente_id',
                'u.name as nombre',
                'u.email',
                'u.phone',
                'p.birthdate',
                'p.gender',
            ])
            ->limit(15)
            ->get()
            ->map(function($paciente) use ($medico) {
                $pacienteId = (int) $paciente->paciente_id;
                
                // Verificar si este paciente ha tenido citas con este médico
                $tieneCitasConMedico = DB::table('citas')
                    ->where('medico_id', $medico->id)
                    ->where('paciente_id', $pacienteId)
                    ->exists();
                
                // Obtener citas del día de hoy con este médico (solo si tiene citas con este médico)
                $citasHoy = collect();
                $totalCitas = 0;
                $citasCompletadas = 0;
                
                if ($tieneCitasConMedico) {
                    $hoy = Carbon::now()->toDateString();
                    $citasHoy = DB::table('citas')
                        ->where('medico_id', $medico->id)
                        ->where('paciente_id', $pacienteId)
                        ->whereDate('starts_at', $hoy)
                        ->select('id', 'starts_at', 'estado', 'especialidad_id')
                        ->get();
                    
                    // Obtener estadísticas del paciente con este médico
                    $totalCitas = DB::table('citas')
                        ->where('medico_id', $medico->id)
                        ->where('paciente_id', $pacienteId)
                        ->count();
                    
                    $citasCompletadas = DB::table('citas')
                        ->where('medico_id', $medico->id)
                        ->where('paciente_id', $pacienteId)
                        ->where('estado', 'completada')
                        ->count();
                }
                
                return [
                    'id' => $pacienteId,
                    'nombre' => (string) $paciente->nombre,
                    'email' => (string) ($paciente->email ?? ''),
                    'phone' => (string) ($paciente->phone ?? ''),
                    'birthdate' => $paciente->birthdate ? Carbon::parse($paciente->birthdate)->format('Y-m-d') : null,
                    'gender' => $paciente->gender ?? null,
                    'tiene_citas_con_medico' => $tieneCitasConMedico,
                    'citas_hoy' => $citasHoy->map(function($cita) {
                        return [
                            'id' => $cita->id,
                            'hora' => Carbon::parse($cita->starts_at)->format('H:i'),
                            'estado' => $cita->estado ?? 'pendiente',
                        ];
                    }),
                    'stats' => [
                        'total_citas' => $totalCitas,
                        'citas_completadas' => $citasCompletadas,
                    ],
                ];
            });
        
        // Buscar citas por nombre de paciente, ID de cita, o fecha
        $citas = Cita::with(['paciente.user', 'especialidad'])
            ->where('medico_id', $medico->id)
            ->where(function($query) use ($q) {
                $query->whereHas('paciente.user', function($userQuery) use ($q) {
                    $userQuery->where('name', 'like', "%{$q}%");
                })
                ->orWhere('id', 'like', "%{$q}%")
                ->orWhere('starts_at', 'like', "%{$q}%");
            })
            ->orderBy('starts_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function(Cita $c) {
                return [
                    'id' => $c->id,
                    'paciente_id' => $c->paciente_id,
                    'paciente_nombre' => $c->paciente?->user?->name ?? 'Paciente',
                    'especialidad' => $c->especialidad?->nombre ?? 'Sin especialidad',
                    'fecha' => $c->starts_at?->format('Y-m-d'),
                    'hora' => $c->starts_at?->format('H:i'),
                    'starts_at' => $c->starts_at?->toIso8601String(),
                    'estado' => $c->estado ?: 'pendiente',
                ];
            });
        
        // También buscar médicos y especialidades (para que el médico pueda ver otros médicos)
        $medicos = [];
        $especialidades = [];
        
        try {
            // Buscar médicos (sin incluir al médico actual)
            $medicos = DB::table('medico_especialidad as me')
                ->join('medicos as m', 'm.id', '=', 'me.medico_id')
                ->join('users as u', 'u.id', '=', 'm.user_id')
                ->join('especialidades as e', 'e.id', '=', 'me.especialidad_id')
                ->where('m.id', '!=', $medico->id)
                ->where(function($query) use ($q) {
                    $query->where('u.name', 'like', "%{$q}%")
                          ->orWhere('e.nombre', 'like', "%{$q}%");
                })
                ->where('m.is_searchable', true)
                ->select([
                    'm.id as id',
                    'u.name as nombre',
                    'e.id as especialidad_id',
                    'e.nombre as especialidad_nombre',
                    'e.slug as especialidad_slug',
                    'm.verif_status',
                ])
                ->distinct()
                ->limit(10)
                ->get()
                ->map(function($medico) {
                    return [
                        'id' => (int) $medico->id,
                        'nombre' => (string) $medico->nombre,
                        'especialidad_id' => (int) $medico->especialidad_id,
                        'especialidad_nombre' => (string) $medico->especialidad_nombre,
                        'especialidad_slug' => (string) $medico->especialidad_slug,
                        'verif_status' => $medico->verif_status ?? null,
                    ];
                });
        } catch (\Throwable $e) {
            // Si falla, continuar sin médicos
        }
        
        try {
            // Buscar especialidades
            $especialidades = DB::table('especialidades')
                ->where('nombre', 'like', "%{$q}%")
                ->select('id', 'nombre', 'slug')
                ->limit(10)
                ->get()
                ->map(function($esp) {
                    return [
                        'id' => (int) ($esp->id ?? 0),
                        'nombre' => (string) ($esp->nombre ?? ''),
                        'slug' => (string) ($esp->slug ?? ''),
                    ];
                });
        } catch (\Throwable $e) {
            // Si falla, continuar sin especialidades
        }
        
        return response()->json([
            'pacientes' => $pacientes,
            'citas' => $citas,
            'medicos' => $medicos,
            'especialidades' => $especialidades,
        ]);
    }

    private function citaDelMedico(Request $req, int $id): Cita
    {
        $medico = Medico::where('user_id', $req->user()->id)->firstOrFail();
        return Cita::where('medico_id', $medico->id)->where('id', $id)->firstOrFail();
    }
}
