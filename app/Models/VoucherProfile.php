<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoucherProfile extends Model
{
    protected $table = 'thre_voucher_profiles';

    protected $fillable = [
        'name', 'hotspot_profile', 'price', 'agent_price',
        'validity', 'shelf_life_days', 'code_length',
    ];

    protected function casts(): array
    {
        return [
            'price'           => 'decimal:2',
            'agent_price'     => 'decimal:2',
            'code_length'     => 'integer',
            'shelf_life_days' => 'integer',
        ];
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }
}
