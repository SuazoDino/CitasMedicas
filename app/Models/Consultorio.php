<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultorio extends Model
{
    protected $table = 'consultorios';

    protected $fillable = [
        'nombre',
        'direccion',
        'ciudad',
        'telefono',
        'latitud',
        'longitud',
        'estado',
    ];
}
