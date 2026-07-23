<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketUpdate extends Model
{
    protected $table = 'thre_ticket_updates';

    protected $fillable = [
        'ticket_id', 'user_id', 'note', 'photo_path', 'status_from', 'status_to',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
