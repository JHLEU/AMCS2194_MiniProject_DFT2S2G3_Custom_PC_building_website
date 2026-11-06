<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SerialNumber;
use App\Models\Warranty;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class WarrantyService
{
    /**
     * Provision warranty coverage for all hardware units in an order.
     */
    public function registerWarrantiesForOrder(Order $order, int $warrantyYears = 2): Collection
    {
        // Load all serial numbers assigned to this order
        $order->loadMissing('serialNumbers.product');

        $registeredWarranties = new Collection();
        $startDate = Carbon::now()->toDateString();
        $endDate = Carbon::now()->addYears($warrantyYears)->toDateString();

        foreach ($order->serialNumbers as $sn) {
            $warranty = Warranty::firstOrCreate(
                [
                    'serial_number_id' => $sn->id,
                    'order_id'         => $order->id,
                ],
                [
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                    'status'     => Warranty::STATUS_ACTIVE,
                ]
            );
            $registeredWarranties->push($warranty);
        }

        return $registeredWarranties;
    }

    /**
     * Batch check and mark expired warranties.
     */
    public function checkAndUpdateExpiry(): int
    {
        $today = Carbon::now()->toDateString();

        return Warranty::where('status', Warranty::STATUS_ACTIVE)
            ->where('end_date', '<', $today)
            ->update(['status' => Warranty::STATUS_EXPIRED]);
    }

    /**
     * Look up warranty by exact serial number string.
     */
    public function lookupBySerialNumber(string $serialNumberCode): ?Warranty
    {
        $serial = SerialNumber::where('serial_number', trim($serialNumberCode))->first();

        if (!$serial) {
            return null;
        }

        return Warranty::with(['serialNumber.product', 'order.customer'])
            ->where('serial_number_id', $serial->id)
            ->first();
    }
}
