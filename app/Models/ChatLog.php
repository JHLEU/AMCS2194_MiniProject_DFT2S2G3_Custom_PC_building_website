<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatLog extends Model
{
    use HasFactory;

    public const SENDER_CUSTOMER = 'Customer';
    public const SENDER_AGENT = 'Agent';
    public const SENDER_BOT = 'Bot';

    public const SENDER_TYPES = [
        self::SENDER_CUSTOMER,
        self::SENDER_AGENT,
        self::SENDER_BOT,
    ];

    protected $fillable = [
        'session_id',
        'customer_id',
        'sender_type',
        'message',
    ];

    /**
     * Relationship to registered customer (nullable for guest visitors).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Scope messages for a specific session ordered chronologically.
     */
    public function scopeForSession(Builder $query, string $sessionId): Builder
    {
        return $query->where('session_id', $sessionId)->orderBy('created_at', 'asc');
    }
}
