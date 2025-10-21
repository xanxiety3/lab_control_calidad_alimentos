<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }


    public function hasPermission(string $permisoNombre): bool
    {
        // Si el usuario es admin, tiene acceso a todo
        if ($this->role && $this->role->nombre === 'admin') {
            return true;
        }

        // Si el usuario no tiene rol asignado
        if (! $this->role) {
            return false;
        }

        // Buscar si el permiso está asociado al rol del usuario
        return $this->role->permissions()
            ->where('nombre', $permisoNombre)
            ->exists();
    }


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
    public function isAdmin()
    {
        return $this->role->nombre === 'admin';
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', true);
    }

    public function scopeInactivos($query)
    {
        return $query->where('estado', false);
    }
}
