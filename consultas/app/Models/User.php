<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name','email','password'];
    protected $hidden = ['password','remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class,'user_role','user_id','role_id');
    }

    // Solo si luego vas a crear estos perfiles:
    public function medico()
    {
        return $this->hasOne(Medico::class, 'user_id');
    }

    public function paciente()
    {
        return $this->hasOne(Paciente::class, 'user_id');
    }
}

