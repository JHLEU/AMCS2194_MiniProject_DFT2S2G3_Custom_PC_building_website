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

    public const SHOPPING_PREFIX = 'shop-';
    public const HANDOFF_MESSAGE = 'You requested human support. A specialist can now see this conversation and reply here.';

    public function scopeHumanQueue(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->where('session_id', 'not like', self::SHOPPING_PREFIX . '%')
                ->orWhereIn('session_id', self::query()->select('session_id')
                    ->where('sender_type', self::SENDER_BOT)->where('message', self::HANDOFF_MESSAGE));
        });
    }

    public static function handedOff(string $sessionId): bool
    {
        return self::where('session_id', $sessionId)->where('sender_type', self::SENDER_BOT)
            ->where('message', self::HANDOFF_MESSAGE)->exists();
    }

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
