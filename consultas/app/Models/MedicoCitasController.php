<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MedicoCitasController extends Controller
{
    public function delDia(Request $r)
    {
        $userId = $r->user()->id;
        $medicoId = DB::table('medicos')->where('user_id',$userId)->value('id');
        if (!$medicoId) return response()->json([], 200);

        $hoy = Carbon::now('America/Lima')->toDateString();

        return DB::table('citas as c')
            ->join('pacientes as p','p.id','=','c.paciente_id')
            ->join('users as up','up.id','=','p.user_id')
            ->where('c.medico_id',$medicoId)
            ->whereDate('c.starts_at',$hoy)
            ->orderBy('c.starts_at')
            ->get(['c.id','c.starts_at','c.ends_at','c.estado','up.name as paciente']);
    }

    public function confirmar(Request $r, $id) { return $this->setEstado($r,$id,'confirmada',['pendiente']); }
    public function completar(Request $r, $id) { return $this->setEstado($r,$id,'completada',['confirmada']); }
    public function cancelar(Request $r, $id)  { return $this->setEstado($r,$id,'cancelada', ['pendiente','confirmada']); }

    private function setEstado(Request $r, $id, $destino, array $permitidos)
    {
        $userId = $r->user()->id;
        $medicoId = DB::table('medicos')->where('user_id',$userId)->value('id');

        $cita = DB::table('citas')->where('id',$id)->first();
        if (!$cita || (int)$cita->medico_id !== (int)$medicoId) return response()->json(['message'=>'No permitido'], 403);
        if (!in_array($cita->estado, $permitidos)) return response()->json(['message'=>'Estado inválido'], 422);

        DB::table('citas')->where('id',$id)->update([
            'estado' => $destino,
            'updated_at' => now(),
        ]);

        return response()->json(['ok'=>true]);
    }
}
