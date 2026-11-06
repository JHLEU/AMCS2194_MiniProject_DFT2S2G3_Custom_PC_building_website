<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LiveChatController extends Controller
{
    /**
     * Fetch conversation thread for a specific session ID.
     */
    public function messages(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id') ?? $request->input('session_id');

        if (!$sessionId) {
            return response()->json([
                'session_id' => null,
                'messages' => [],
            ]);
        }

        $logs = ChatLog::forSession((string) $sessionId)->get();

        $messages = $logs->map(function (ChatLog $log) {
            return [
                'id' => $log->id,
                'sender_type' => $log->sender_type,
                'is_customer' => $log->sender_type === ChatLog::SENDER_CUSTOMER,
                'message' => $log->message,
                'time' => $log->created_at ? $log->created_at->format('g:i A') : '',
                'created_at' => $log->created_at ? $log->created_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'session_id' => $sessionId,
            'messages' => $messages,
        ]);
    }

    /**
     * Store incoming customer message and return updated thread.
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:1000'],
            'session_id' => ['nullable', 'string', 'max:100'],
        ]);

        $sessionId = !empty($validated['session_id']) 
            ? (string) $validated['session_id'] 
            : 'chat_' . Str::random(20);

        $cleanMessage = trim(strip_tags($validated['message']));

        if ($cleanMessage === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Message content cannot be blank.',
            ], 422);
        }

        $chatLog = ChatLog::create([
            'session_id' => $sessionId,
            'customer_id' => Auth::id(),
            'sender_type' => ChatLog::SENDER_CUSTOMER,
            'message' => $cleanMessage,
        ]);

        $logs = ChatLog::forSession($sessionId)->get();
        $messages = $logs->map(function (ChatLog $log) {
            return [
                'id' => $log->id,
                'sender_type' => $log->sender_type,
                'is_customer' => $log->sender_type === ChatLog::SENDER_CUSTOMER,
                'message' => $log->message,
                'time' => $log->created_at ? $log->created_at->format('g:i A') : '',
                'created_at' => $log->created_at ? $log->created_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'session_id' => $sessionId,
            'message' => [
                'id' => $chatLog->id,
                'sender_type' => $chatLog->sender_type,
                'message' => $chatLog->message,
                'time' => $chatLog->created_at->format('g:i A'),
            ],
            'messages' => $messages,
        ], 201);
    }
}
