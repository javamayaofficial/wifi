<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'thre_audit_logs';

    protected $fillable = [
        'user_id', 'user_name', 'event', 'model_type',
        'model_id', 'label', 'changes', 'ip',
    ];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
