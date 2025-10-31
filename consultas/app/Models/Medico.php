<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medico extends Model
{
    protected $table = 'medicos';
    protected $fillable = ['user_id','verif_status'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function citas(): HasMany { return $this->hasMany(Cita::class); }
}
