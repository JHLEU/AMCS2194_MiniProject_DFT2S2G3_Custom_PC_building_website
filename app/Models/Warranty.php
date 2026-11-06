<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warranty extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'Active';
    public const STATUS_EXPIRED = 'Expired';
    public const STATUS_VOID = 'Void';

    protected $fillable = [
        'serial_number_id',
        'order_id',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function serialNumber(): BelongsTo
    {
        return $this->belongsTo(SerialNumber::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }

        return $this->end_date && Carbon::now()->isAfter($this->end_date);
    }

    public function daysRemaining(): int
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return 0;
        }

        $now = Carbon::now()->startOfDay();
        $end = Carbon::parse($this->end_date)->startOfDay();

        return $now->isAfter($end) ? 0 : $now->diffInDays($end);
    }
}
