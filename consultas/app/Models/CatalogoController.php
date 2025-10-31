<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CatalogoController extends Controller
{
    public function especialidades()
    {
        return DB::table('especialidades')->select('id','nombre')->orderBy('nombre')->get();
    }

    public function medicosPorEspecialidad($especialidadId)
    {
        if (Schema::hasTable('medico_especialidad')) {
            return DB::table('medicos as m')
                ->join('users as u','u.id','=','m.user_id')
                ->join('medico_especialidad as me','me.medico_id','=','m.id')
                ->where('me.especialidad_id', $especialidadId)
                ->orderBy('u.name')
                ->get(['m.id','u.name as nombre','u.email']);
        }
        return DB::table('medicos as m')
            ->join('users as u','u.id','=','m.user_id')
            ->where('m.especialidad_id', $especialidadId)
            ->orderBy('u.name')
            ->get(['m.id','u.name as nombre','u.email']);
    }
}
