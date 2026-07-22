<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $table = 'thre_transactions';

    protected $fillable = [
        'order_id', 'customer_id', 'amount', 'amount_final',
        'payment_method', 'status', 'raw_response', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'amount_final' => 'decimal:2',
            'raw_response' => 'array',
            'paid_at'      => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function markPaid(array $raw = []): void
    {
        $this->update([
            'status'       => 'paid',
            'paid_at'      => now(),
            'raw_response' => $raw ?: $this->raw_response,
        ]);
    }
}
