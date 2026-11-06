<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const STATUS_PENDING    = 'Pending';
    public const STATUS_PAID       = 'Paid';
    public const STATUS_ASSEMBLING = 'Assembling';
    public const STATUS_TESTING    = 'Testing';
    public const STATUS_SHIPPED    = 'Shipped';
    public const STATUS_COMPLETED  = 'Completed';
    public const STATUS_CANCELLED  = 'Cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAID,
        self::STATUS_ASSEMBLING,
        self::STATUS_TESTING,
        self::STATUS_SHIPPED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public const ACTIVE_STATUSES = [
        self::STATUS_PAID,
        self::STATUS_ASSEMBLING,
        self::STATUS_TESTING,
        self::STATUS_SHIPPED,
    ];

    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'shipping_address',
        'order_number',
        'total_selling_amount',
        'total_cost_amount',
        'order_status',
        'tracking_number',
        'carrier',
    ];

    protected function casts(): array
    {
        return [
            'total_selling_amount' => 'decimal:2',
            'total_cost_amount'    => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function serialNumbers(): HasMany
    {
        return $this->hasMany(SerialNumber::class);
    }

    /**
     * Scope for active in-progress orders (Paid, Assembling, Testing, Shipped).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('order_status', self::ACTIVE_STATUSES);
    }

    /**
     * Scope for completed orders.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('order_status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to filter by status or keyword.
     */
    public function scopeFilter(Builder $query, ?string $status = null, ?string $search = null): Builder
    {
        if ($status && in_array($status, self::STATUSES, true)) {
            $query->where('order_status', $status);
        }

        if ($search) {
            $term = '%' . trim($search) . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('order_number', 'like', $term)
                  ->orWhere('customer_name', 'like', $term)
                  ->orWhere('customer_phone', 'like', $term)
                  ->orWhere('customer_email', 'like', $term)
                  ->orWhere('tracking_number', 'like', $term);
            });
        }

        return $query;
    }

    public function getGrossProfitAttribute(): float
    {
        return (float) ($this->total_selling_amount - $this->total_cost_amount);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->order_status) {
            self::STATUS_PAID       => 'badge-paid',
            self::STATUS_ASSEMBLING => 'badge-assembling',
            self::STATUS_TESTING    => 'badge-testing',
            self::STATUS_SHIPPED    => 'badge-shipped',
            self::STATUS_COMPLETED  => 'badge-completed',
            self::STATUS_CANCELLED  => 'badge-cancelled',
            default                 => 'badge-secondary',
        };
    }
}
