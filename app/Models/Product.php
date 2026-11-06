<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public const CATEGORIES = [
        'CPU',
        'GPU',
        'Motherboard',
        'RAM',
        'Storage',
        'PSU',
        'Case',
        'Cooling',
        'Accessories',
    ];

    public const SOCKET_TYPES = [
        'AM5',
        'AM4',
        'LGA1700',
        'LGA1851',
        'LGA1200',
    ];

    public const RAM_TYPES = [
        'DDR4',
        'DDR5',
    ];

    protected $fillable = [
        'name',
        'category',
        'category_id',
        'supplier_id',
        'brand',
        'specs',
        'image_url',
        'tdp_wattage',
        'socket_type',
        'ram_type',
        'cost_price',
        'selling_price',
        'warranty_months',
        'low_stock_threshold',
    ];

    protected function casts(): array
    {
        return [
            'tdp_wattage'         => 'integer',
            'cost_price'          => 'decimal:2',
            'selling_price'       => 'decimal:2',
            'warranty_months'     => 'integer',
            'low_stock_threshold' => 'integer',
        ];
    }

    public function categoryModel(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function defaultSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function supplierMappings(): HasMany
    {
        return $this->hasMany(SupplierHardwareMapping::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_hardware_mappings')
            ->withPivot(['id', 'supplier_sku', 'agreed_unit_price', 'lead_time_days'])
            ->withTimestamps();
    }

    public function serialNumbers(): HasMany
    {
        return $this->hasMany(SerialNumber::class);
    }

    public function restockOrderItems(): HasMany
    {
        return $this->hasMany(RestockOrderItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope to attach live counts of serial numbers per lifecycle state.
     */
    public function scopeWithStockCounts(Builder $query): Builder
    {
        return $query->withCount([
            'serialNumbers as in_stock_count' => fn (Builder $q) => $q->where('status', SerialNumber::STATUS_IN_STOCK),
            'serialNumbers as reserved_count' => fn (Builder $q) => $q->where('status', SerialNumber::STATUS_RESERVED),
            'serialNumbers as sold_count'     => fn (Builder $q) => $q->where('status', SerialNumber::STATUS_SOLD),
            'serialNumbers as rma_count'      => fn (Builder $q) => $q->where('status', SerialNumber::STATUS_RMA),
            'serialNumbers as lost_count'     => fn (Builder $q) => $q->where('status', SerialNumber::STATUS_LOST),
        ]);
    }

    /**
     * Portable low-stock filter across SQLite and MySQL:
     * Products whose In-Stock serial number count is <= low_stock_threshold.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->withStockCounts()
            ->whereRaw(
                '(SELECT COUNT(*) FROM serial_numbers WHERE serial_numbers.product_id = products.id AND serial_numbers.status = ?) <= products.low_stock_threshold',
                [SerialNumber::STATUS_IN_STOCK]
            );
    }

    public function getIsLowStockAttribute(): bool
    {
        $inStock = $this->attributes['in_stock_count']
            ?? $this->serialNumbers()->where('status', SerialNumber::STATUS_IN_STOCK)->count();

        return (int) $inStock <= (int) $this->low_stock_threshold;
    }

    public function getDeficitAttribute(): int
    {
        $inStock = $this->attributes['in_stock_count']
            ?? $this->serialNumbers()->where('status', SerialNumber::STATUS_IN_STOCK)->count();

        return max(0, (int) $this->low_stock_threshold - (int) $inStock);
    }
}
