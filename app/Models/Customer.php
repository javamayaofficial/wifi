<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $table = 'thre_customers';

    protected $fillable = [
        'name', 'username', 'password', 'plan_id', 'router_id',
        'expired_date', 'status', 'phone', 'email', 'unique_code',
    ];

    /** PPPoE password terenkripsi (reversible) supaya bisa dipush ke MikroTik. */
    protected function casts(): array
    {
        return [
            'password'     => 'encrypted',
            'expired_date' => 'date',
            'unique_code'  => 'integer',
        ];
    }

    protected $hidden = ['password'];

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
        return $this->hasMany(Transaction::class);
    }

    public function isExpired(): bool
    {
        return $this->expired_date->isPast();
    }
}
