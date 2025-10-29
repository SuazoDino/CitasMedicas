<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
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

    protected $casts = [
        'verified_at'            => 'datetime',
        'provisional_expires_at' => 'datetime',
        'is_searchable'          => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
