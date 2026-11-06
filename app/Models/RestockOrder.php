<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestockOrder extends Model
{
    public const STATUS_PENDING   = 'Pending';
    public const STATUS_APPROVED  = 'Approved';
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_CANCELLED = 'Cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'order_code',
        'supplier_id',
        'status',
        'total_amount',
        'order_date',
        'expected_date',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount'  => 'decimal:2',
            'order_date'    => 'date',
            'expected_date' => 'date',
            'completed_at'  => 'datetime',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RestockOrderItem::class);
    }

    public function serialNumbers(): HasMany
    {
        return $this->hasMany(SerialNumber::class);
    }

    public static function generateOrderCode(): string
    {
        $suffix = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        return 'RO-' . date('Ymd') . '-' . $suffix;
    }
}
