<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class SerialNumber extends Model
{
    public const STATUS_IN_STOCK = 'In-Stock';
    public const STATUS_RESERVED = 'Reserved';
    public const STATUS_SOLD     = 'Sold';
    public const STATUS_RMA      = 'RMA';
    public const STATUS_LOST     = 'Lost';

    public const STATUSES = [
        self::STATUS_IN_STOCK,
        self::STATUS_RESERVED,
        self::STATUS_SOLD,
        self::STATUS_RMA,
        self::STATUS_LOST,
    ];

    protected $fillable = [
        'sn_code',
        'product_id',
        'status',
        'batch_cost',
        'restock_order_id',
        'order_id',
    ];

    protected function casts(): array
    {
        return [
            'batch_cost' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function restockOrder(): BelongsTo
    {
        return $this->belongsTo(RestockOrder::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function warranty(): HasOne
    {
        return $this->hasOne(Warranty::class);
    }

    /**
     * Accessor alias for backward compatibility with views and callers.
     */
    public function getSerialNumberAttribute(): string
    {
        return (string) $this->sn_code;
    }

    /**
     * Bulk register serial numbers while skipping existing duplicates.
     *
     * @param  int        $productId
     * @param  string[]   $snCodes
     * @param  float      $batchCost
     * @param  int|null   $restockOrderId
     * @param  string     $status
     * @return array{inserted: int, skipped: int}
     */
    public static function bulkRegister(
        int $productId,
        array $snCodes,
        float $batchCost = 0.0,
        ?int $restockOrderId = null,
        string $status = self::STATUS_IN_STOCK
    ): array {
        $inserted = 0;
        $skipped = 0;

        foreach ($snCodes as $rawCode) {
            $cleanCode = trim((string) $rawCode);
            if ($cleanCode === '') {
                continue;
            }

            if (self::where('sn_code', $cleanCode)->exists()) {
                $skipped++;
                continue;
            }

            self::create([
                'sn_code'          => $cleanCode,
                'product_id'       => $productId,
                'status'           => $status,
                'batch_cost'       => $batchCost,
                'restock_order_id' => $restockOrderId,
            ]);
            $inserted++;
        }

        return [
            'inserted' => $inserted,
            'skipped'  => $skipped,
        ];
    }

    /**
     * Calculate inventory costing & valuation summary across all In-Stock units.
     * Compatible with both SQLite and MySQL.
     */
    public static function getInventoryCostingSummary(): array
    {
        $inStockQuery = self::where('status', self::STATUS_IN_STOCK);
        $totalUnits = (int) (clone $inStockQuery)->count();
        $totalValuation = (float) ((clone $inStockQuery)->sum('batch_cost') ?? 0.0);
        $avgUnitCost = $totalUnits > 0 ? round($totalValuation / $totalUnits, 2) : 0.0;

        $byCategory = DB::table('serial_numbers as sn')
            ->join('products as p', 'sn.product_id', '=', 'p.id')
            ->where('sn.status', self::STATUS_IN_STOCK)
            ->select(
                'p.category',
                DB::raw('COUNT(sn.id) as in_stock_count'),
                DB::raw('COALESCE(SUM(sn.batch_cost), 0) as category_valuation')
            )
            ->groupBy('p.category')
            ->orderByDesc('category_valuation')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();

        $byProduct = DB::table('serial_numbers as sn')
            ->join('products as p', 'sn.product_id', '=', 'p.id')
            ->where('sn.status', self::STATUS_IN_STOCK)
            ->select(
                'p.id as product_id',
                'p.name as product_name',
                'p.category',
                DB::raw('COUNT(sn.id) as in_stock_count'),
                DB::raw('COALESCE(MIN(sn.batch_cost), 0) as min_batch_cost'),
                DB::raw('COALESCE(MAX(sn.batch_cost), 0) as max_batch_cost'),
                DB::raw('COALESCE(AVG(sn.batch_cost), 0) as avg_batch_cost'),
                DB::raw('COALESCE(SUM(sn.batch_cost), 0) as total_product_valuation')
            )
            ->groupBy('p.id', 'p.name', 'p.category')
            ->orderByDesc('total_product_valuation')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();

        return [
            'overall' => [
                'total_units'     => $totalUnits,
                'total_valuation' => $totalValuation,
                'avg_unit_cost'   => $avgUnitCost,
            ],
            'by_category' => $byCategory,
            'by_product'  => $byProduct,
        ];
    }
}
