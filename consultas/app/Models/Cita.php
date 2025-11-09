<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cita extends Model
{
    protected $table = 'citas';
    protected $fillable = [
        'medico_id','paciente_id','especialidad_id',
        'starts_at','ends_at','estado','reminder_sent_at','notas',
        'created_by_user_id','canceled_by_user_id','cancel_reason',
        'rating','review','rated_at',
        // Historial médico
        'sintomas','diagnostico','tratamiento','observaciones_medicas','historial_completado_at'
    ];
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'reminder_sent_at'=>'datetime',
        'rated_at' => 'datetime',
        'historial_completado_at' => 'datetime',
        'rating' => 'integer',
    ];

    public function medico(): BelongsTo { return $this->belongsTo(Medico::class); }
    public function paciente(): BelongsTo { return $this->belongsTo(Paciente::class); }
    public function especialidad(): BelongsTo { return $this->belongsTo(Especialidad::class); }
}
