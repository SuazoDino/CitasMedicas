<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model
{
    protected $table = 'pacientes';
    protected $fillable = ['user_id','doc_tipo','doc_numero','birthdate','gender'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function citas(): HasMany { return $this->hasMany(Cita::class); }
}
