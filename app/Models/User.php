<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->isAdminQC();
        }

        if ($panel->getId() === 'leader') {
            return $this->isLeader();
        }

        return false;
    }

    const ROLE_ADMIN = 'admin_qc';
    const ROLE_LEADER = 'leader';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Check if user is Admin QC
     */
    public function isAdminQC(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is Leader
     */
    public function isLeader(): bool
    {
        return $this->role === self::ROLE_LEADER;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get all inspections performed by this user (inspector).
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'user_id');
    }
}
