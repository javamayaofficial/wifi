<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use LogsActivity;

    protected $table = 'thre_tickets';

    protected $fillable = [
        'ticket_number', 'customer_id', 'title', 'description', 'category',
        'priority', 'status', 'assigned_to', 'created_by', 'resolution',
        'assigned_at', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public const CATEGORIES = [
        'internet_mati' => 'Internet Mati',
        'lambat'        => 'Internet Lambat',
        'perangkat'     => 'Kerusakan Perangkat',
        'instalasi'     => 'Instalasi / Pindah',
        'lainnya'       => 'Lainnya',
    ];

    public const STATUSES = ['baru', 'ditugaskan', 'proses', 'selesai', 'batal'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatesLog(): HasMany
    {
        return $this->hasMany(TicketUpdate::class)->latest();
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, ['selesai', 'batal'], true);
    }

    /** Lama tiket terbuka, untuk pemantauan SLA. */
    public function ageInHours(): int
    {
        return (int) $this->created_at->diffInHours($this->resolved_at ?? now());
    }

    public function auditLabel(): string
    {
        return $this->ticket_number . ' — ' . $this->title;
    }

    public static function generateNumber(): string
    {
        return 'TKT-' . now()->format('Ymd') . '-' . str_pad((string) (static::whereDate('created_at', today())->count() + 1), 3, '0', STR_PAD_LEFT);
    }
}
