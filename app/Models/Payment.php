<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const STATUS_PENDING = 'Pending';
    public const STATUS_SUCCESS = 'Success';
    public const STATUS_FAILED  = 'Failed';

    public const METHOD_DUMMY_CARD     = 'Mock Credit/Debit Card';
    public const METHOD_DUMMY_FPX      = 'Mock FPX Online Banking';
    public const METHOD_DUMMY_SANDBOX  = 'Demo Sandbox Instant';
    public const METHOD_STRIPE_MOCK    = 'Stripe (Sandbox Mode)';

    public const METHODS = [
        self::METHOD_DUMMY_SANDBOX,
        self::METHOD_DUMMY_CARD,
        self::METHOD_DUMMY_FPX,
        self::METHOD_STRIPE_MOCK,
    ];

    protected $fillable = [
        'order_id',
        'payment_method',
        'transaction_id',
        'amount',
        'payment_status',
        'payment_date',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'payment_date' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isSuccessful(): bool
    {
        return $this->payment_status === self::STATUS_SUCCESS;
    }
}
