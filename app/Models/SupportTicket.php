<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupportTicket extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'Open';
    public const STATUS_DIAGNOSING = 'Diagnosing';
    public const STATUS_WAITING_FOR_PARTS = 'WaitingForParts';
    public const STATUS_RESOLVED = 'Resolved';
    public const STATUS_CLOSED = 'Closed';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_DIAGNOSING,
        self::STATUS_WAITING_FOR_PARTS,
        self::STATUS_RESOLVED,
        self::STATUS_CLOSED,
    ];

    protected $fillable = [
        'customer_id',
        'warranty_id',
        'subject',
        'issue_description',
        'status',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(TechSchedule::class);
    }

    public function latestSchedule(): HasOne
    {
        return $this->hasOne(TechSchedule::class)->latestOfMany();
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'badge-warning',
            self::STATUS_DIAGNOSING => 'badge-info',
            self::STATUS_WAITING_FOR_PARTS => 'badge-danger',
            self::STATUS_RESOLVED => 'badge-success',
            self::STATUS_CLOSED => 'badge-secondary',
            default => 'badge-secondary',
        };
    }
}
