<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Router extends Model
{
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
        ];
    }

    protected $hidden = ['password'];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
