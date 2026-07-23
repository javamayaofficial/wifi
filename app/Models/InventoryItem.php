<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    use LogsActivity;

    protected $table = 'thre_inventory';

    protected $fillable = [
        'name', 'type', 'serial', 'status', 'customer_id',
        'purchase_date', 'purchase_price', 'note',
    ];

    protected function casts(): array
    {
        return ['purchase_date' => 'date', 'purchase_price' => 'decimal:2'];
    }

    public const TYPES = [
        'router'  => 'Router',
        'onu'     => 'ONU / Modem',
        'radio'   => 'Radio / Antena',
        'switch'  => 'Switch',
        'kabel'   => 'Kabel',
        'adaptor' => 'Adaptor',
        'lainnya' => 'Lainnya',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function auditLabel(): string
    {
        return $this->name . ($this->serial ? " ({$this->serial})" : '');
    }
}
