<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    public const ROLE_ADMIN = 'Admin';
    public const ROLE_WAREHOUSE = 'Staff_Warehouse';
    public const ROLE_TECH = 'Staff_Tech';
    public const ROLE_CUSTOMER = 'Customer';

    protected $fillable = [
        'name',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
