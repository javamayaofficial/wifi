<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',   // admin: bcrypt (beda dgn PPPoE)
            'is_active'         => 'boolean',
        ];
    }

    public const ROLES = [
        'owner'   => 'Owner — akses penuh',
        'admin'   => 'Admin — operasional harian',
        'kasir'   => 'Kasir — pembayaran & tagihan',
        'teknisi' => 'Teknisi — tiket & monitoring',
    ];

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }
}
