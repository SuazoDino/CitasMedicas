<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $table = 'pacientes';

    // En tu tabla se ven columnas: user_id, doc_tipo, doc_numero, birthdate, gender
    protected $fillable = [
        'user_id',
        'doc_tipo',
        'doc_numero',
        'birthdate',
        'gender',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
