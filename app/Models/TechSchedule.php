<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'technician_id',
        'scheduled_datetime',
        'estimated_duration_hours',
        'notes',
    ];

    protected $casts = [
        'scheduled_datetime' => 'datetime',
        'estimated_duration_hours' => 'integer',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
