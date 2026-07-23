<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use LogsActivity;

    protected $table = 'thre_transactions';

    protected $fillable = [
        'order_id', 'customer_id', 'amount', 'amount_final',
        'payment_method', 'status', 'raw_response', 'paid_at',
        'late_fee', 'discount', 'note',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'amount_final' => 'decimal:2',
            'late_fee'     => 'decimal:2',
            'discount'     => 'decimal:2',
            'raw_response' => 'array',
            'paid_at'      => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** Total yang harus dibayar = harga + denda - diskon. */
    public function grandTotal(): float
    {
        return (float) $this->amount + (float) $this->late_fee - (float) $this->discount;
    }

    public function auditLabel(): string
    {
        return $this->order_id;
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
