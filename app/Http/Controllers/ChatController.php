<?php

namespace App\Http\Controllers;

use App\Models\ChatLog;
use App\Services\GeminiClient;
use App\Services\ShoppingAssistant;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ChatController extends Controller
{
    public function history(Request $request): JsonResponse
    {
        $sessionId = $this->sessionId($request);

        $messages = ChatLog::where('session_id', $sessionId)
            ->latest('id')->limit(50)->get(['sender_type', 'message'])->reverse()->values();

        return response()->json([
            'messages' => $messages,
            'human_support' => ChatLog::handedOff($sessionId),
        ])->header('Cache-Control', 'no-store');
    }

    public function handoff(Request $request): JsonResponse
    {
        $sessionId = $this->sessionId($request);
        if (!ChatLog::handedOff($sessionId)) {
            ChatLog::create([
                'session_id' => $sessionId,
                'customer_id' => $request->user()?->id,
                'sender_type' => ChatLog::SENDER_BOT,
                'message' => ChatLog::HANDOFF_MESSAGE,
            ]);
        }

        return $this->history($request);
    }

    public function send(Request $request, ShoppingAssistant $assistant, GeminiClient $gemini): JsonResponse
    {
        $message = trim($request->validate(['message' => ['required', 'string', 'max:1000']])['message']);
        $sessionId = $this->sessionId($request);

        if (ChatLog::handedOff($sessionId)) {
            ChatLog::create([
                'session_id' => $sessionId,
                'customer_id' => $request->user()?->id,
                'sender_type' => ChatLog::SENDER_CUSTOMER,
                'message' => $message,
            ]);

            return $this->history($request);
        }

        if (!$gemini->isConfigured()) {
            return response()->json([
                'code' => 'assistant_not_configured',
                'message' => 'The AI assistant is unavailable. Select "Talk to a specialist" to continue.',
            ], 503);
        }

        $history = ChatLog::where('session_id', $sessionId)
            ->latest('id')->limit(6)->get()->reverse();

        try {
            $reply = $assistant->reply($message, $history);
        } catch (ConnectionException | RuntimeException $exception) {
            // Provider errors may include credentials or conversation data in their diagnostics.
            return response()->json(['message' => 'The shopping assistant is temporarily unavailable. Please try again.'], 503);
        }

        DB::transaction(function () use ($request, $sessionId, $message, $reply) {
            $identity = ['session_id' => $sessionId, 'customer_id' => $request->user()?->id];
            ChatLog::create($identity + ['sender_type' => ChatLog::SENDER_CUSTOMER, 'message' => $message]);
            ChatLog::create($identity + ['sender_type' => ChatLog::SENDER_BOT, 'message' => $reply]);
        });

        return response()->json(['reply' => $reply])->header('Cache-Control', 'no-store');
    }

    private function sessionId(Request $request): string
    {
        $key = 'shopping_chat.' . ($request->user()?->id ?? 'guest');

        return $request->session()->remember($key, fn () => ChatLog::SHOPPING_PREFIX . Str::uuid());
    }
}
