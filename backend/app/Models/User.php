<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Campos que se pueden asignar masivamente
     *
     * erp_coduser: referencia al CODUSER de BASEUSUARIOS (ERP Clarion)
     * Los usuarios de Laravel se sincronizan con el ERP en el primer login.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'erp_coduser',  // Referencia al ERP Clarion (BASEUSUARIOS.CODUSER)
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
