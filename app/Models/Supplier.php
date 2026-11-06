<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'company_name',
        'contact_person',
        'phone',
        'email',
        'address',
    ];

    public function hardwareMappings(): HasMany
    {
        return $this->hasMany(SupplierHardwareMapping::class);
    }

    public function mappedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'supplier_hardware_mappings')
            ->withPivot(['id', 'supplier_sku', 'agreed_unit_price', 'lead_time_days'])
            ->withTimestamps();
    }

    public function restockOrders(): HasMany
    {
        return $this->hasMany(RestockOrder::class);
    }
}
