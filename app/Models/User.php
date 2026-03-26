<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    public const ROLE_ADMINISTRADOR = 'Administrador';
    public const ROLE_GESTOR = 'Gestor';

    protected $fillable = [
        'name',
        'email',
        'job_title',
        'is_active',
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Los atributos que deben estar ocultos para arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getRoleAttribute(): string
    {
        return $this->normalizeRole($this->job_title);
    }

    public static function availableRoles(): array
    {
        return [
            self::ROLE_ADMINISTRADOR,
            self::ROLE_GESTOR,
        ];
    }

    public function isAdministrator(): bool
    {
        return $this->role === self::ROLE_ADMINISTRADOR;
    }

    public function isGestor(): bool
    {
        return $this->role === self::ROLE_GESTOR;
    }

    protected function normalizeRole(?string $value): string
    {
        $normalized = trim((string) $value);

        if (strcasecmp($normalized, self::ROLE_GESTOR) === 0) {
            return self::ROLE_GESTOR;
        }

        return self::ROLE_ADMINISTRADOR;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Devuelve un array de claims personalizados que se agregarán al JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
