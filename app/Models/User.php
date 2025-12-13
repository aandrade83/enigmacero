<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Campos que se pueden asignar en masa (fill / create / update).
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',       // admin, employee, client
        'is_active',  // 1 = activo, 0 = desactivado
    ];

    /**
     * Campos que NO se exponen cuando conviertes el modelo a array/json.
     * (por seguridad)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts: cómo interpretar ciertos campos.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_active'         => 'boolean',
        // Si quisieras que Laravel hashee automáticamente:
        // 'password' => 'hashed',
    ];

    /**
     * Helpers de rol, para usarlos luego en middlewares / vistas.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }
}
