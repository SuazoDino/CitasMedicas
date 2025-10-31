<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PacienteCitasController extends Controller
{
    public function proximas(Request $r)
    {
        $userId = $r->user()->id;
        $pacienteId = DB::table('pacientes')->where('user_id',$userId)->value('id');
        if (!$pacienteId) return response()->json([], 200);

        return DB::table('citas as c')
            ->join('medicos as m','m.id','=','c.medico_id')
            ->join('users as um','um.id','=','m.user_id')
            ->where('c.paciente_id', $pacienteId)
            ->whereIn('c.estado', ['pendiente','confirmada'])
            ->where('c.starts_at','>=', Carbon::now('America/Lima'))
            ->orderBy('c.starts_at')
            ->get(['c.id','c.starts_at','c.ends_at','c.estado','um.name as medico']);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'medico_id'       => 'required|integer|exists:medicos,id',
            'starts_at'       => 'required|date',        // ISO
            'especialidad_id' => 'nullable|integer',
        ]);

        $userId = $r->user()->id;
        $pacienteId = DB::table('pacientes')->where('user_id',$userId)->value('id');
        if (!$pacienteId) return response()->json(['message'=>'No eres paciente'], 422);

        $start = Carbon::parse($data['starts_at'], 'America/Lima');
        $slot  = DB::table('medico_horarios')->where('medico_id',$data['medico_id'])->value('slot_min') ?? 30;
        $end   = $start->copy()->addMinutes((int)$slot);

        // Solape con citas existentes
        $solapa = DB::table('citas')
            ->where('medico_id', $data['medico_id'])
            ->whereIn('estado',['pendiente','confirmada'])
            ->where('starts_at','<',$end)
            ->where('ends_at','>',$start)
            ->exists();
        if ($solapa) return response()->json(['message'=>'Horario no disponible'], 422);

        $id = DB::table('citas')->insertGetId([
            'paciente_id'     => $pacienteId,
            'medico_id'       => $data['medico_id'],
            'especialidad_id' => $data['especialidad_id'] ?? null,
            'starts_at'       => $start,
            'ends_at'         => $end,
            'estado'          => 'pendiente',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json(['ok'=>true,'id'=>$id,'starts_at'=>$start,'ends_at'=>$end], 201);
    }

    public function cancelar(Request $r, $id)
    {
        $userId = $r->user()->id;
        $pacienteId = DB::table('pacientes')->where('user_id',$userId)->value('id');

        $cita = DB::table('citas')->where('id',$id)->first();
        if (!$cita || (int)$cita->paciente_id !== (int)$pacienteId) {
            return response()->json(['message'=>'No puedes cancelar esta cita'], 403);
        }
        if (!in_array($cita->estado, ['pendiente','confirmada'])) {
            return response()->json(['message'=>'La cita no se puede cancelar'], 422);
        }

        DB::table('citas')->where('id',$id)->update([
            'estado' => 'cancelada',
            'updated_at' => now(),
        ]);

        return response()->json(['ok'=>true]);
    }
}
