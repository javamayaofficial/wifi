<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    protected $table = 'thre_vouchers';

    protected $fillable = [
        'voucher_profile_id', 'router_id', 'reseller_id', 'batch', 'code',
        'password', 'status', 'sale_price', 'sold_by',
        'handed_over_at', 'sold_at', 'used_at',
    ];

    protected function casts(): array
    {
        return [
            'sold_at'        => 'datetime',
            'used_at'        => 'datetime',
            'handed_over_at' => 'datetime',
            'sale_price'     => 'decimal:2',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(VoucherProfile::class, 'voucher_profile_id');
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    /** Agen/warung yang dititipi voucher ini. */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    /** Sudah diakui sebagai pendapatan (terjual atau sudah terpakai). */
    public function scopeSold($q)
    {
        return $q->whereIn('status', ['terjual', 'terpakai'])->whereNotNull('sold_at');
    }
}
