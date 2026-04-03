<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Les rôles disponibles
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_OPERATEUR = 'operateur';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'actif',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'actif' => 'boolean',
        ];
    }

    /**
     * Vérifie si l'utilisateur est admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Vérifie si l'utilisateur est manager
     */
    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    /**
     * Vérifie si l'utilisateur est opérateur
     */
    public function isOperateur(): bool
    {
        return $this->role === self::ROLE_OPERATEUR;
    }

    /**
     * Vérifie si l'utilisateur a un rôle donné
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Vérifie si l'utilisateur peut gérer les utilisateurs
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Vérifie si l'utilisateur peut accéder aux paramètres
     */
    public function canAccessSettings(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    /**
     * Obtenir le libellé du rôle
     */
    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_OPERATEUR => 'Opérateur',
            default => 'Inconnu',
        };
    }

    /**
     * Liste des rôles disponibles
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_OPERATEUR => 'Opérateur',
        ];
    }

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}
