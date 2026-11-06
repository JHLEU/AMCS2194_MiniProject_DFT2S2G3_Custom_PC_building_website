@extends('layouts.erp')

@section('title', 'Customer Service Live Chat Workbench - ERP')

@section('content')
<div class="erp-container" style="max-width: 1400px; margin: 0 auto; padding: 24px;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 700; color: #f8fafc; margin: 0 0 6px 0;">Customer Service Live Chat Desk</h1>
            <p style="color: #94a3b8; margin: 0; font-size: 0.95rem;">Monitor live storefront visitor inquiries and reply as an official PC support specialist.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.4); padding: 6px 14px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                Live Service Gateway Active
            </span>
        </div>
    </div>

    <!-- Dual Pane Chat Workbench -->
    <div style="display: grid; grid-template-columns: 360px 1fr; gap: 20px; height: 750px; background: #0f172a; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        
        <!-- Left Pane: Conversation Queue -->
        <div style="background: #131d31; border-right: 1px solid rgba(255, 255, 255, 0.08); display: flex; flex-direction: column; overflow: hidden;">
            <div style="padding: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); background: #17223b;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 1rem; font-weight: 600; color: #f1f5f9; margin: 0;">Inquiry Threads</h3>
                    <span style="font-size: 0.8rem; background: #2563eb; color: #ffffff; padding: 2px 8px; border-radius: 12px; font-weight: 600;">
                        {{ $sessions->count() }} active
                    </span>
                </div>
            </div>

            <!-- Thread List -->
            <div style="flex: 1; overflow-y: auto; padding: 8px;">
                @forelse($sessions as $sess)
                    @php
                        $isActive = $sess->session_id === $activeSessionId;
                    @endphp
                    <a href="{{ route('erp.chat.index', ['session_id' => $sess->session_id]) }}" 
                       style="display: block; padding: 12px 14px; margin-bottom: 6px; border-radius: 8px; text-decoration: none; transition: all 0.2s; 
                              background: {{ $isActive ? '#1e2e4f' : 'transparent' }}; 
                              border: 1px solid {{ $isActive ? '#3b82f6' : 'transparent' }};">
                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                            <span style="font-weight: 600; font-size: 0.9rem; color: {{ $isActive ? '#60a5fa' : '#f8fafc' }};">
                                @if($sess->customer)
                                    👤 {{ $sess->customer->name }}
                                @else
                                    🌐 Guest ({{ substr($sess->session_id, 0, 10) }}...)
                                @endif
                            </span>
                            <span style="font-size: 0.72rem; color: #94a3b8;">
                                {{ $sess->last_activity ? $sess->last_activity->diffForHumans(null, true) : '' }}
                            </span>
                        </div>
                        <div style="font-size: 0.8rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            @if($sess->last_sender === 'Agent')
                                <span style="color: #38bdf8;">Staff: </span>
                            @endif
                            {{ $sess->last_message ?: 'New inquiry opened...' }}
                        </div>
                        <div style="margin-top: 6px; display: flex; gap: 8px; font-size: 0.72rem;">
                            @if($sess->customer)
                                <span style="background: rgba(37, 99, 235, 0.2); color: #93c5fd; padding: 2px 6px; border-radius: 4px;">Registered</span>
                            @else
                                <span style="background: rgba(148, 163, 184, 0.2); color: #cbd5e1; padding: 2px 6px; border-radius: 4px;">Storefront Visitor</span>
                            @endif
                            <span style="color: #64748b;">{{ $sess->total_messages }} msgs</span>
                        </div>
                    </a>
                @empty
                    <div style="text-align: center; padding: 40px 16px; color: #64748b;">
                        <div style="font-size: 2rem; margin-bottom: 8px;">💬</div>
                        <p style="font-size: 0.9rem; margin: 0;">No customer inquiries yet.</p>
                        <p style="font-size: 0.8rem; margin-top: 4px;">Inquiries sent via the storefront widget will appear here in real time.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Pane: Active Thread & Reply Desk -->
        <div style="display: flex; flex-direction: column; height: 100%; background: #0f172a;">
            @if($activeSessionId && $activeMessages->isNotEmpty())
                <!-- Active Conversation Header -->
                <div style="padding: 16px 20px; background: #17223b; border-bottom: 1px solid rgba(255, 255, 255, 0.08); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <h3 style="font-size: 1.1rem; font-weight: 600; color: #f8fafc; margin: 0;">
                                @if($activeCustomer)
                                    {{ $activeCustomer->name }}
                                @else
                                    Storefront Guest Visitor
                                @endif
                            </h3>
                            <span style="font-size: 0.75rem; background: #0b0f19; border: 1px solid rgba(255,255,255,0.1); color: #94a3b8; padding: 2px 8px; border-radius: 4px; font-family: monospace;">
                                Session: {{ substr($activeSessionId, 0, 18) }}...
                            </span>
                        </div>
                        <div style="font-size: 0.82rem; color: #94a3b8; margin-top: 4px; display: flex; gap: 16px;">
                            @if($activeCustomer)
                                <span>📧 {{ $activeCustomer->email }}</span>
                                <span>🛒 {{ $activeCustomer->orders_count ?? 0 }} Orders</span>
                                <span>🛡️ {{ $activeCustomer->support_tickets_count ?? 0 }} RMA Tickets</span>
                            @else
                                <span>🌐 Unauthenticated visitor from public storefront</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Messages Stream -->
                <div id="erp-chat-messages" style="flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 16px; background: #0b0f19;">
                    @foreach($activeMessages as $msg)
                        @php
                            $isStaff = $msg->sender_type === 'Agent';
                        @endphp
                        <div style="display: flex; flex-direction: column; align-items: {{ $isStaff ? 'flex-end' : 'flex-start' }};">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px; font-size: 0.75rem; color: #94a3b8;">
                                @if($isStaff)
                                    <span style="color: #10b981; font-weight: 600;">👨‍💼 Staff Specialist (You)</span>
                                @else
                                    <span style="color: #60a5fa; font-weight: 600;">
                                        👤 {{ $activeCustomer ? $activeCustomer->name : 'Customer' }}
                                    </span>
                                @endif
                                <span>{{ $msg->created_at ? $msg->created_at->format('M d, g:i A') : '' }}</span>
                            </div>
                            <div style="max-width: 75%; padding: 12px 16px; border-radius: {{ $isStaff ? '14px 14px 2px 14px' : '14px 14px 14px 2px' }}; 
                                        background: {{ $isStaff ? '#065f46' : '#1e293b' }}; 
                                        border: 1px solid {{ $isStaff ? 'rgba(16, 185, 129, 0.3)' : 'rgba(255, 255, 255, 0.08)' }}; 
                                        color: #f8fafc; font-size: 0.92rem; line-height: 1.5; white-space: pre-wrap; word-break: break-word;">
                                {{ $msg->message }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Staff Reply Bar -->
                <div style="padding: 16px 20px; background: #17223b; border-top: 1px solid rgba(255, 255, 255, 0.08);">
                    <form method="POST" action="{{ route('erp.chat.reply', ['sessionId' => $activeSessionId]) }}" style="display: flex; gap: 12px;">
                        @csrf
                        <textarea name="message" rows="2" placeholder="Write an official customer support reply..." required
                                  style="flex: 1; background: #0f172a; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; padding: 10px 14px; font-size: 0.9rem; color: #f8fafc; resize: none; outline: none;"></textarea>
                        <button type="submit" style="background: #10b981; color: #ffffff; border: none; border-radius: 8px; padding: 0 24px; font-weight: 600; font-size: 0.92rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;">
                            <span>Send Reply</span>
                        </button>
                    </form>
                </div>
            @else
                <!-- No active session selected -->
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #64748b; padding: 40px; text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 12px;">💬</div>
                    <h3 style="color: #cbd5e1; margin-bottom: 8px;">No Thread Selected</h3>
                    <p style="max-width: 420px; margin: 0; font-size: 0.9rem;">
                        Select a conversation thread from the left queue to review chat transcripts and send live replies to customers.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const messageContainer = document.getElementById('erp-chat-messages');
        if (messageContainer) {
            messageContainer.scrollTop = messageContainer.scrollHeight;
        }
    });
</script>
@endsection
