<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResellerTransaction extends Model
{
    protected $table = 'thre_reseller_transactions';

    protected $fillable = [
        'reseller_id', 'type', 'amount', 'balance_after', 'description', 'created_by',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'balance_after' => 'decimal:2'];
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }
}
