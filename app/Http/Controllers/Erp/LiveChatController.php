<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\ChatLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LiveChatController extends Controller
{
    /**
     * Display the ERP Customer Service Chat Workbench.
     */
    public function index(Request $request): View
    {
        // Fetch all distinct sessions with latest message activity
        $sessionSummaries = ChatLog::select(
            'session_id',
            DB::raw('MAX(id) as last_log_id'),
            DB::raw('MAX(created_at) as last_activity'),
            DB::raw('COUNT(id) as total_messages')
        )
        ->groupBy('session_id')
        ->orderBy('last_activity', 'desc')
        ->get();

        // Hydrate session previews
        $sessions = $sessionSummaries->map(function ($item) {
            $lastLog = ChatLog::find($item->last_log_id);
            $customer = null;

            $customerId = ChatLog::where('session_id', $item->session_id)
                ->whereNotNull('customer_id')
                ->value('customer_id');

            if ($customerId) {
                $customer = User::find($customerId);
            }

            return (object) [
                'session_id' => $item->session_id,
                'total_messages' => $item->total_messages,
                'last_activity' => $lastLog ? $lastLog->created_at : null,
                'last_message' => $lastLog ? $lastLog->message : '',
                'last_sender' => $lastLog ? $lastLog->sender_type : '',
                'customer' => $customer,
            ];
        });

        // Determine active session
        $activeSessionId = $request->query('session_id');
        if (!$activeSessionId && $sessions->isNotEmpty()) {
            $activeSessionId = $sessions->first()->session_id;
        }

        $activeMessages = collect();
        $activeCustomer = null;

        if ($activeSessionId) {
            $activeMessages = ChatLog::forSession($activeSessionId)->with('customer')->get();

            $activeCustomerId = ChatLog::where('session_id', $activeSessionId)
                ->whereNotNull('customer_id')
                ->value('customer_id');

            if ($activeCustomerId) {
                $activeCustomer = User::withCount(['orders', 'supportTickets'])->find($activeCustomerId);
            }
        }

        return view('erp.chat.index', [
            'sessions' => $sessions,
            'activeSessionId' => $activeSessionId,
            'activeMessages' => $activeMessages,
            'activeCustomer' => $activeCustomer,
        ]);
    }

    /**
     * Fetch conversation thread JSON for live polling in workbench.
     */
    public function history(string $sessionId): JsonResponse
    {
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
            'session_id' => $sessionId,
            'messages' => $messages,
        ]);
    }

    /**
     * Post a staff reply into the customer session.
     */
    public function reply(Request $request, string $sessionId): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $customerId = ChatLog::where('session_id', $sessionId)
            ->whereNotNull('customer_id')
            ->value('customer_id');

        $chatLog = ChatLog::create([
            'session_id' => $sessionId,
            'customer_id' => $customerId,
            'sender_type' => ChatLog::SENDER_AGENT,
            'message' => trim($validated['message']),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => [
                    'id' => $chatLog->id,
                    'sender_type' => $chatLog->sender_type,
                    'message' => $chatLog->message,
                    'time' => $chatLog->created_at->format('g:i A'),
                ],
            ]);
        }

        return redirect()->route('erp.chat.index', ['session_id' => $sessionId])
            ->with('success', 'Support reply sent successfully.');
    }
}
