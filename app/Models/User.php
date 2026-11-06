<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'username',
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'customer_id');
    }

    public function techSchedules(): HasMany
    {
        return $this->hasMany(TechSchedule::class, 'technician_id');
    }

    public function hasRole(string|array $roles): bool
    {
        if (!$this->role) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($this->role->name, $roles, true);
        }

        return $this->role->name === $roles;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ROLE_ADMIN);
    }

    public function isWarehouse(): bool
    {
        return $this->hasRole(Role::ROLE_WAREHOUSE);
    }

    public function isTech(): bool
    {
        return $this->hasRole(Role::ROLE_TECH);
    }

    public function isCustomer(): bool
    {
        return $this->hasRole(Role::ROLE_CUSTOMER);
    }

    public function isStaff(): bool
    {
        return $this->hasRole([Role::ROLE_ADMIN, Role::ROLE_WAREHOUSE, Role::ROLE_TECH]);
    }
}
