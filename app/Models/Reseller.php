<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reseller extends Model
{
    use LogsActivity;

    protected $table = 'thre_resellers';

    protected $fillable = [
        'name', 'phone', 'email', 'area',
        'commission_percent', 'deposit_balance', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'commission_percent' => 'decimal:2',
            'deposit_balance'    => 'decimal:2',
            'is_active'          => 'boolean',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ResellerTransaction::class)->latest();
    }
}
