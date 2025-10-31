<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogoController extends Controller
{
    
    public function especialidades()
    {
        return DB::table('especialidades')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'slug']);
    }

    public function medicos(Request $request)
    {
        $query = DB::table('medico_especialidad as me')
            ->join('medicos as m', 'm.id', '=', 'me.medico_id')
            ->join('users as u', 'u.id', '=', 'm.user_id')
            ->select([
                'm.id as id',
                'u.name as nombre',
                'me.especialidad_id',
            ])
            ->orderBy('u.name');

        if ($request->filled('especialidad_id')) {
            $query->where('me.especialidad_id', $request->integer('especialidad_id'));
        }

        return $query->get();
    }
}
