<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PacienteCitasController extends Controller
{
    private function pacienteId($userId){
        return DB::table('pacientes')->where('user_id',$userId)->value('id');
    }

    public function proximas(Request $r){
        $pid = $this->pacienteId($r->user()->id);
        if (!$pid) return response()->json([]);

        $rows = DB::table('citas as c')
          ->join('medicos as m','m.id','=','c.medico_id')
          ->join('users as u','u.id','=','m.user_id')
          ->join('especialidades as e','e.id','=','c.especialidad_id')
          ->where('c.paciente_id',$pid)
          ->where('c.starts_at','>=',now())
          ->orderBy('c.starts_at')
          ->get(['c.id','c.starts_at','c.ends_at','c.estado','e.nombre as especialidad','u.name as medico']);

        return $rows->map(function($x){
            return [
              'id'=>$x->id,
              'fecha'=>Carbon::parse($x->starts_at)->format('Y-m-d'),
              'hora'=>Carbon::parse($x->starts_at)->format('H:i'),
              'estado'=>$x->estado,
              'especialidad'=>$x->especialidad,
              'medico'=>$x->medico,
            ];
        });
    }

    public function store(Request $r){
        $uid = $r->user()->id;
        $pid = $this->pacienteId($uid);
        if (!$pid) return response()->json(['message'=>'No es paciente'], 403);

        $data = $r->validate([
            'medico_id'       => 'required|exists:medicos,id',
            'especialidad_id' => 'required|exists:especialidades,id',
            'starts_at'       => 'required|date',
            'motivo'          => 'nullable|string|max:140',
        ]);

        $start = Carbon::parse($data['starts_at']);
        $dow   = (int) $start->dayOfWeek;
        $slot  = DB::table('medico_horarios')
                  ->where('medico_id',$data['medico_id'])
                  ->where('dia_semana',$dow)->where('activo',true)
                  ->where('hora_inicio','<=',$start->format('H:i:s'))
                  ->where('hora_fin','>',$start->format('H:i:s'))
                  ->first();
        if (!$slot) {
            return response()->json(['message'=>'El horario seleccionado no está disponible'], 422);
        }
        $slotMin = $slot?->slot_min ?? 30;
        $end = (clone $start)->addMinutes($slotMin);

        $conflict = DB::table('citas')
            ->where('medico_id',$data['medico_id'])
            ->where('starts_at','<',$end)
            ->where('ends_at','>',$start)
            ->exists();
        if ($conflict) return response()->json(['message'=>'Horario ocupado'], 422);

        $id = DB::table('citas')->insertGetId([
            'medico_id'=>$data['medico_id'],
            'paciente_id'=>$pid,
            'especialidad_id'=>$data['especialidad_id'],
            'starts_at'=>$start, 'ends_at'=>$end,
            'estado'=>'pendiente', 'motivo'=>$data['motivo']??null,
            'created_by_user_id'=>$uid,
            'created_at'=>now(),'updated_at'=>now(),
        ]);

        return response()->json(['id'=>$id], 201);
    }

    public function cancelar(Request $r, $id){
        $uid = $r->user()->id;
        $pid = $this->pacienteId($uid);
        $cita = DB::table('citas')->where('id',$id)->first();
        if (!$cita || $cita->paciente_id != $pid) return response()->json(['message'=>'No autorizado'],403);

        DB::table('citas')->where('id',$id)->update([
            'estado'=>'cancelada',
            'canceled_by_user_id'=>$uid,
            'cancel_reason'=>$r->input('motivo'),
            'updated_at'=>now(),
        ]);
        return response()->json(['ok'=>true]);
    }
}
