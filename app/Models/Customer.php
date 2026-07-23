<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use LogsActivity;

    protected $table = 'thre_customers';

    protected $fillable = [
        'name', 'username', 'password', 'plan_id', 'router_id',
        'expired_date', 'status', 'phone', 'email', 'unique_code',
        'address', 'national_id_number', 'latitude', 'longitude', 'odp_name', 'odp_port',
        'device_type', 'device_serial', 'installation_photo', 'installed_at',
        'identity_card_path', 'profile_completed_at', 'portal_password', 'reseller_id',
    ];

    /** PPPoE password terenkripsi (reversible) supaya bisa dipush ke MikroTik. */
    protected function casts(): array
    {
        return [
            'password'     => 'encrypted',
            'expired_date' => 'date',
            'synced_at'    => 'datetime',
            'installed_at'    => 'date',
            'latitude'        => 'decimal:7',
            'longitude'       => 'decimal:7',
            'profile_completed_at' => 'datetime',
            'portal_password' => 'hashed',
            'unique_code'  => 'integer',
        ];
    }

    protected $hidden = ['password', 'portal_password'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function hasIdentityCard(): bool
    {
        return filled($this->identity_card_path);
    }

    public function profileIsComplete(): bool
    {
        return filled($this->name)
            && filled($this->address)
            && filled($this->national_id_number)
            && $this->hasIdentityCard()
            && $this->hasCoordinates();
    }

    public function auditLabel(): string
    {
        return $this->name . ' (' . $this->username . ')';
    }

    public function isExpired(): bool
    {
        return $this->expired_date->isPast();
    }
}
