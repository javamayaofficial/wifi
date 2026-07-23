<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use LogsActivity;

    protected $table = 'thre_expenses';

    protected $fillable = [
        'date', 'category', 'description', 'amount', 'attachment', 'created_by',
    ];

    protected function casts(): array
    {
        return ['date' => 'date', 'amount' => 'decimal:2'];
    }

    public const CATEGORIES = [
        'bandwidth' => 'Bandwidth / Upstream',
        'listrik'   => 'Listrik',
        'gaji'      => 'Gaji & Honor',
        'sewa'      => 'Sewa Tiang / Tempat',
        'perangkat' => 'Perangkat & Material',
        'transport' => 'Transport & BBM',
        'lainnya'   => 'Lainnya',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function auditLabel(): string
    {
        return $this->description . ' — Rp ' . number_format((float) $this->amount, 0, ',', '.');
    }
}
