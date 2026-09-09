<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';

    protected $fillable = [
        'paciente_id',
        'profesional_id',
        'consultorio_id',
        'fecha_hora_inicio',
        'fecha_hora_fin',
        'estado',
        'motivo_consulta',
        'notas_medico',
        'motivo_cancelacion_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora_inicio' => 'datetime',
            'fecha_hora_fin' => 'datetime',
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(ProfesionalMedico::class, 'profesional_id');
    }

    public function consultorio(): BelongsTo
    {
        return $this->belongsTo(Consultorio::class, 'consultorio_id');
    }

    public function pago()
    {
        return $this->hasOne(Pago::class, 'cita_id');
    }
}
