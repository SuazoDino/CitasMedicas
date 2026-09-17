<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medico extends Model
{
    use HasFactory;

    protected $table = 'medicos';

    protected $fillable = [
        'user_id',
        'id_doc_tipo',
        'id_doc_numero',
        'id_doc_file',
        'lic_tipo',
        'lic_numero',
        'lic_pais',
        'lic_file',
        'verif_status',
        'verif_notas',
        'verified_at',
        'is_searchable',
        'provisional_expires_at',
        'provisional_max_citas',
        'invite_code',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'provisional_expires_at' => 'datetime',
            'is_searchable' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function especialidades(): BelongsToMany
    {
        return $this->belongsToMany(Especialidad::class, 'medico_especialidad', 'medico_id', 'especialidad_id');
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'medico_id');
    }
}
