<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfesionalMedico extends Model
{
    use HasFactory;

    protected $table = 'profesionales_medicos';

    protected $fillable = [
        'usuario_id',
        'especialidad_id',
        'nombres',
        'apellidos',
        'numero_colegiatura',
        'telefono',
        'biografia',
        'estado_validacion',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'profesional_id');
    }

    /**
     * Helper para obtener el nombre completo.
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }
}
