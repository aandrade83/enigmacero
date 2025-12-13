<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Campos que se pueden asignar en masa.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',       // admin, employee, client
        'is_active',  // 1 = activo, 0 = desactivado
    ];

    /**
     * Campos ocultos al convertir a array / json.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts de tipos.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_active'         => 'boolean',
    ];

    /**
     * Helpers de rol.
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
