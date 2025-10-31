<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController; // 👈 OJO: Api, no Auth
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\PacienteCitasController;
use App\Http\Controllers\Api\MedicoCitasController;
use Carbon\Carbon;
use App\Http\Controllers\Api\MedicoSlotsController;
use App\Http\Controllers\Api\Medico\CitasController as MedicoCitas;
use App\Http\Controllers\Api\Paciente\CitasController as PacienteCitas;

Route::middleware('auth:web')->group(function () {

    // Paciente
    Route::get('/paciente/citas/proximas', [PacienteCitas::class, 'proximas']);

    // Médico
    Route::get('/medico/citas', [MedicoCitas::class, 'index']);
    Route::post('/medico/citas/{id}/confirmar', [MedicoCitas::class, 'confirmar']);
    Route::post('/medico/citas/{id}/completar', [MedicoCitas::class, 'completar']);
    Route::post('/medico/citas/{id}/cancelar',  [MedicoCitas::class, 'cancelar']);
});

Route::get('/catalogo/especialidades', [CatalogoController::class,'especialidades']);
Route::get('/catalogo/medicos/{especialidad}', [CatalogoController::class,'medicosPorEspecialidad']);

Route::middleware('auth:sanctum')->group(function(){
    // Paciente
    Route::get('/paciente/citas/proximas', [PacienteCitasController::class,'proximas']);
    Route::post('/citas',                    [PacienteCitasController::class,'store']);
    Route::post('/citas/{id}/cancelar',      [PacienteCitasController::class,'cancelar']);

    // Médico
    Route::get('/medico/citas',              [MedicoCitasController::class,'delDia']);
    Route::post('/medico/citas/{id}/confirmar', [MedicoCitasController::class,'confirmar']);
    Route::post('/medico/citas/{id}/cancelar',  [MedicoCitasController::class,'cancelar']);
    Route::post('/medico/citas/{id}/completar', [MedicoCitasController::class,'completar']);
    Route::get('/medicos/{medico}/slots', [MedicoSlotsController::class, 'slots']);

});


// Helper: roles
function userRoles($userId){
  return DB::table('user_role')
    ->join('roles','roles.id','=','user_role.role_id')
    ->where('user_role.user_id',$userId)
    ->pluck('roles.name')->all();
}
function pacienteId($userId){
  return DB::table('pacientes')->where('user_id',$userId)->value('id');
}
function medicoId($userId){
  return DB::table('medicos')->where('user_id',$userId)->value('id');
}

Route::middleware('auth:sanctum')->group(function () {

  // Próximas citas del paciente (para /me)
  Route::get('/paciente/citas/proximas', function (Request $r) {
    $pid = pacienteId($r->user()->id);
    if (!$pid) return response()->json([], 200);

    $rows = DB::table('citas')
      ->join('medicos','medicos.id','=','citas.medico_id')
      ->join('users','users.id','=','medicos.user_id')
      ->join('especialidades','especialidades.id','=','citas.especialidad_id')
      ->where('citas.paciente_id',$pid)
      ->where('citas.starts_at','>=',now())
      ->orderBy('citas.starts_at')
      ->limit(20)
      ->get([
        'citas.id','citas.starts_at','citas.ends_at','citas.estado',
        'especialidades.nombre as especialidad',
        'users.name as medico'
      ]);

    // map a formato amigable
    $out = $rows->map(function($x){
      return [
        'id' => $x->id,
        'fecha' => \Carbon\Carbon::parse($x->starts_at)->format('Y-m-d'),
        'hora'  => \Carbon\Carbon::parse($x->starts_at)->format('H:i'),
        'estado'=> $x->estado,
        'especialidad' => $x->especialidad,
        'medico' => $x->medico,
      ];
    });

    return response()->json($out);
  });

  // Reservar cita (paciente)
  Route::post('/citas', function (Request $r) {
    $uid = $r->user()->id;
    $pid = pacienteId($uid);
    if (!$pid) return response()->json(['message'=>'No es paciente'], 403);

    $data = $r->validate([
      'medico_id'       => 'required|exists:medicos,id',
      'especialidad_id' => 'required|exists:especialidades,id',
      'starts_at'       => 'required|date',     // ISO string
      'motivo'          => 'nullable|string|max:140',
    ]);

    // slot del médico para ese día
    $start = \Carbon\Carbon::parse($data['starts_at']);
    $dow   = (int) $start->dayOfWeek; // 0=Dom
    $slot  = DB::table('medico_horarios')
      ->where('medico_id', $data['medico_id'])
      ->where('dia_semana', $dow)
      ->where('activo', true)
      ->where('hora_inicio','<=',$start->format('H:i:s'))
      ->where('hora_fin','>',$start->format('H:i:s'))
      ->orderBy('hora_inicio')
      ->first();

    $slotMin = $slot?->slot_min ?? 30;
    $end = (clone $start)->addMinutes($slotMin);

    // conflicto con otras citas del médico
    $conflict = DB::table('citas')
      ->where('medico_id', $data['medico_id'])
      ->where(function($q) use($start,$end){
        $q->where('starts_at','<',$end)->where('ends_at','>',$start);
      })
      ->exists();

    if ($conflict) {
      return response()->json(['message'=>'Horario ocupado'], 422);
    }

    $id = DB::table('citas')->insertGetId([
      'medico_id'       => $data['medico_id'],
      'paciente_id'     => $pid,
      'especialidad_id' => $data['especialidad_id'],
      'starts_at'       => $start,
      'ends_at'         => $end,
      'estado'          => 'pendiente',
      'motivo'          => $data['motivo'] ?? null,
      'created_by_user_id' => $uid,
      'created_at'      => now(),
      'updated_at'      => now(),
    ]);

    return response()->json(['id'=>$id], 201);
  });

  // Citas del médico (agenda del día)
  Route::get('/medico/citas', function (Request $r) {
    $mid = medicoId($r->user()->id);
    if (!$mid) return response()->json([], 200);

    $date = $r->query('date', now()->format('Y-m-d'));
    $start = \Carbon\Carbon::parse($date.' 00:00:00');
    $end   = (clone $start)->endOfDay();

    $rows = DB::table('citas')
      ->join('pacientes','pacientes.id','=','citas.paciente_id')
      ->join('users','users.id','=','pacientes.user_id')
      ->where('citas.medico_id',$mid)
      ->whereBetween('citas.starts_at', [$start, $end])
      ->orderBy('citas.starts_at')
      ->get([
        'citas.id','citas.starts_at','citas.ends_at','citas.estado',
        'users.name as paciente'
      ]);

    return response()->json($rows);
  });

});


// 🔎 Diagnóstico: ping simple para confirmar que api.php se carga
Route::get('/_ping', fn() => response()->json(['ok' => true, 'ts' => now()]));

// Público
Route::prefix('auth')->group(function () {
    Route::post('register/paciente', [AuthController::class, 'registerPaciente']);
    Route::post('register/medico',   [AuthController::class, 'registerMedico']);
    Route::post('login',             [AuthController::class, 'login']);
});

// Protegido
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('me',     [AuthController::class, 'me']);
        Route::post('logout',[AuthController::class, 'logout']);
    });
});
