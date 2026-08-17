<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomResetPassword;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
    ];

    protected $appends = [
        'nombre_completo',
    ];

    // Nombre completo calculado, útil para mostrar en tablas/reportes
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function tieneRol(string $nombreRol): bool
    {
        return $this->roles()->where('nombre', $nombreRol)->exists();
    }

    // Docente: asignaturas que imparte
    public function asignaturas()
    {
        return $this->belongsToMany(Asignatura::class, 'docente_asignatura');
    }

    // Director: carrera que dirige (uno a uno)
    public function carreraDirigida()
    {
        return $this->hasOne(Carrera::class, 'director_id');
    }

    // Secuencias en las que es autor
    public function secuencias()
    {
        return $this->belongsToMany(Secuencia::class, 'secuencia_user');
    }

    public function comentarios()
    {
        return $this->hasMany(SecuenciaComentario::class);
    }

    public function deviceTokens()
    {
        return $this->hasMany(\App\Models\DeviceToken::class);
    }

    public function routeNotificationForFcm($notification)
    {
        // Retorna una cadena con el token, o un array de tokens si un usuario tiene varios dispositivos
        return $this->deviceTokens()->pluck('fcm_token')->all();
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }
}
