<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MedicoCitasController extends Controller
{
    private function medicoId($userId){
        return DB::table('medicos')->where('user_id',$userId)->value('id');
    }

    public function delDia(Request $r){
        $mid = $this->medicoId($r->user()->id);
        if (!$mid) return response()->json([]);

        $date = $r->query('date', now()->format('Y-m-d'));
        $start = Carbon::parse($date.' 00:00:00');
        $end   = (clone $start)->endOfDay();

        return DB::table('citas as c')
          ->join('pacientes as p','p.id','=','c.paciente_id')
          ->join('users as u','u.id','=','p.user_id')
          ->where('c.medico_id',$mid)
          ->whereBetween('c.starts_at',[$start,$end])
          ->orderBy('c.starts_at')
          ->get(['c.id','c.starts_at','c.ends_at','c.estado','u.name as paciente']);
    }

    public function confirmar(Request $r, $id){
        $mid = $this->medicoId($r->user()->id);
        $ok = DB::table('citas')->where('id',$id)->where('medico_id',$mid)->update([
            'estado'=>'confirmada','updated_at'=>now()
        ]);
        return $ok ? response()->json(['ok'=>true]) : response()->json(['message'=>'No autorizado'],403);
    }

    public function completar(Request $r, $id){
        $mid = $this->medicoId($r->user()->id);
        $ok = DB::table('citas')->where('id',$id)->where('medico_id',$mid)->update([
            'estado'=>'completada','updated_at'=>now()
        ]);
        return $ok ? response()->json(['ok'=>true]) : response()->json(['message'=>'No autorizado'],403);
    }

    public function cancelar(Request $r, $id){
        $mid = $this->medicoId($r->user()->id);
        $ok = DB::table('citas')->where('id',$id)->where('medico_id',$mid)->update([
            'estado'=>'cancelada','canceled_by_user_id'=>$r->user()->id,
            'cancel_reason'=>$r->input('motivo'),'updated_at'=>now()
        ]);
        return $ok ? response()->json(['ok'=>true]) : response()->json(['message'=>'No autorizado'],403);
    }
}
