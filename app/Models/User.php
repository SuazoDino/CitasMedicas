<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotificataion;
use App\Models\NotificationPreference;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    protected $table = 'usuarios';
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['email','password','rol_id','estado'];
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
        return $this->belongsTo(Role::class, 'rol_id');
    }

    // Solo si luego vas a crear estos perfiles:
    public function medico(): HasOne
    {
        return $this->hasOne(Medico::class, 'user_id');
    }

    public function paciente(): HasOne
    {
        return $this->hasOne(Paciente::class, 'user_id');
    }
    
    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        // TODO: Import or fix ResetPasswordNotification if needed.
        //$this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'rol_id' => $this->rol_id,
        ];
    }
}

