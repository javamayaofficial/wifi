<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'username', 'password', 'role', 'phone', 'is_active', 'customer_access_only'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',   // admin: bcrypt (beda dgn PPPoE)
            'is_active'         => 'boolean',
            'customer_access_only' => 'boolean',
        ];
    }

    public const ROLES = [
        'owner'   => 'Owner — akses penuh',
        'admin'   => 'Admin — operasional harian',
        'kasir'   => 'Kasir — pembayaran & tagihan',
        'teknisi' => 'Teknisi — operasional lapangan',
    ];

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isCustomerAccessOnly(): bool
    {
        return (bool) $this->customer_access_only;
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }
}
