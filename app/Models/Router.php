<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Router extends Model
{
    use LogsActivity;

    protected $table = 'thre_routers';

    protected $fillable = [
        'name', 'ip', 'api_port', 'username', 'password', 'use_tls',
    ];

    /** Password disimpan terenkripsi (reversible), bukan hash. */
    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'api_port' => 'integer',
            'use_tls'  => 'boolean',
            'is_up'           => 'boolean',
            'last_checked_at' => 'datetime',
            'down_since'      => 'datetime',
            'alert_sent_at'   => 'datetime',
        ];
    }

    protected $hidden = ['password'];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }
}
