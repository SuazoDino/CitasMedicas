<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogoController extends Controller
{
    public function especialidades() {
        return DB::table('especialidades')->orderBy('nombre')->get(['id','nombre','slug']);
    }

    public function medicosPorEspecialidad($especialidad_id) {
        return DB::table('medico_especialidad as me')
            ->join('medicos as m','m.id','=','me.medico_id')
            ->join('users as u','u.id','=','m.user_id')
            ->where('me.especialidad_id',$especialidad_id)
            ->orderBy('u.name')
            ->get([
                'm.id as medico_id',
                'u.name as medico',
            ]);
    }
}
