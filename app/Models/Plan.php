<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $table = 'thre_plans';

    protected $fillable = [
        'name', 'price', 'bandwidth', 'duration_days', 'mikrotik_profile',
    ];

    protected function casts(): array
    {
        return [
            'price'         => 'decimal:2',
            'duration_days' => 'integer',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
