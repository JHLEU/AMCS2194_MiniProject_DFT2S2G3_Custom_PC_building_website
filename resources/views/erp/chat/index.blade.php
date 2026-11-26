@extends('layouts.erp')

@section('title', 'Customer Service Live Chat Workbench - ERP')
@section('page-title', 'Customer Live Chat Desk')

@section('styles')
<style>
    /* =========================================================================
       视口全局锁死：禁止外层任何滚动条，确保输入框固定在可见视野底部
       ========================================================================= */
    html, body {
        height: 100% !important;
        max-height: 100vh !important;
        overflow: hidden !important;
    }

    .erp-container {
        height: 100vh !important;
        max-height: 100vh !important;
        overflow: hidden !important;
    }

    .erp-main {
        height: 100vh !important;
        max-height: 100vh !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
    }

    .erp-content {
        padding: 0 !important;
        flex: 1 1 0% !important;
        min-height: 0 !important;
        height: calc(100vh - var(--header-height)) !important;
        max-height: calc(100vh - var(--header-height)) !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
    }

    .erp-content > .alert {
        flex-shrink: 0;
        margin: 8px 16px;
    }

    /* 聊天总工作台 */
    .chat-workbench-container {
        display: flex !important;
        flex: 1 1 0% !important;
        min-height: 0 !important;
        width: 100% !important;
        height: 100% !important;
        max-height: 100% !important;
        background: var(--bg-page);
        overflow: hidden !important;
        position: relative;
    }

    /* =========================================================================
       窗口 1：选择聊天对象的聊天选择窗口 (独立的滑动窗口，独立上下滑动)
       ========================================================================= */
    #chat-window-selection {
        width: 340px;
        min-width: 340px;
        height: 100% !important;
        max-height: 100% !important;
        background: var(--bg-deep);
        border-right: 1px solid var(--border-color);
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        transition: width 0.24s cubic-bezier(0.4, 0, 0.2, 1), min-width 0.24s cubic-bezier(0.4, 0, 0.2, 1);
        flex-shrink: 0 !important;
        position: relative;
        z-index: 10;
    }

    /* 缩小状态：向左靠齐，只显示用户名 */
    #chat-window-selection.is-collapsed {
        width: 110px;
        min-width: 110px;
    }

    .chat-sidebar-header {
        padding: 12px 14px;
        background: var(--bg-card);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        height: 56px;
        box-sizing: border-box;
        flex-shrink: 0 !important;
    }

    .chat-sidebar-title-area {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        overflow: hidden;
    }

    .chat-sidebar-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .chat-badge-count {
        font-size: 0.7rem;
        background: var(--accent-badge);
        color: var(--primary);
        border: 1px solid rgba(34, 197, 94, 0.25);
        padding: 2px 7px;
        border-radius: 10px;
        font-weight: 600;
        white-space: nowrap;
        font-family: 'Geist Mono', monospace, sans-serif;
    }

    /* 缩小/放大切换按钮 */
    .chat-sidebar-toggle-btn {
        background: var(--bg-raised);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        width: 30px;
        height: 30px;
        border-radius: var(--radius-sm);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.18s ease;
        flex-shrink: 0;
        padding: 0;
    }

    .chat-sidebar-toggle-btn:hover {
        background: var(--bg-card);
        color: var(--primary);
        border-color: var(--primary);
    }

    #chat-window-selection.is-collapsed .chat-sidebar-title-area {
        display: none;
    }

    #chat-window-selection.is-collapsed .chat-sidebar-header {
        justify-content: center;
        padding: 12px 6px;
    }

    /* 窗口 1 专属独立的滑动槽列表 (独立上下滑动，不影响任何其他区域) */
    #selection-scroll-area {
        flex: 1 1 0% !important;
        min-height: 0 !important;
        height: auto !important;
        padding: 8px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        overflow-y: scroll !important;
        overflow-x: hidden !important;
    }

    .chat-thread-item {
        display: block;
        padding: 12px 14px;
        border-radius: var(--radius-sm);
        text-decoration: none;
        transition: all 0.18s ease;
        background: var(--bg-deep);
        border: 1px solid transparent;
        position: relative;
    }

    .chat-thread-item:hover {
        background: var(--bg-raised);
        border-color: var(--border-color);
    }

    .chat-thread-item.is-active {
        background: var(--bg-card);
        border-color: var(--primary);
        box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.2);
    }

    .chat-thread-item.is-active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 8px;
        bottom: 8px;
        width: 3px;
        background: var(--primary);
        border-radius: 0 2px 2px 0;
    }

    .thread-user-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 6px;
        margin-bottom: 4px;
    }

    .thread-user-name {
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-width: 0;
    }

    .chat-thread-item.is-active .thread-user-name {
        color: var(--primary);
    }

    .thread-time {
        font-size: 0.72rem;
        color: var(--text-muted);
        white-space: nowrap;
        flex-shrink: 0;
        font-family: 'Geist Mono', monospace, sans-serif;
    }

    .thread-last-msg {
        font-size: 0.8125rem;
        color: var(--text-secondary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 6px;
        line-height: 1.4;
    }

    .thread-last-msg .agent-prefix {
        color: var(--primary);
        font-weight: 500;
    }

    .thread-badges {
        display: flex;
        gap: 6px;
        font-size: 0.7rem;
        align-items: center;
    }

    .badge-user-type {
        background: rgba(255, 255, 255, 0.06);
        color: var(--text-secondary);
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px solid var(--border-color);
        font-weight: 500;
    }

    .badge-user-type.registered {
        background: var(--accent-badge);
        color: var(--primary);
        border-color: rgba(34, 197, 94, 0.2);
    }

    .badge-msg-count {
        color: var(--text-muted);
        font-family: 'Geist Mono', monospace, sans-serif;
    }

    /* 缩小状态下的联系人展现：仅显示用户名字与缩写头像 */
    .collapsed-avatar {
        display: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--bg-raised);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        font-weight: 600;
        font-size: 0.82rem;
        align-items: center;
        justify-content: center;
        margin: 0 auto 4px auto;
        flex-shrink: 0;
    }

    .chat-thread-item.is-active .collapsed-avatar {
        background: var(--accent-badge);
        color: var(--primary);
        border-color: var(--primary);
    }

    #chat-window-selection.is-collapsed .collapsed-avatar {
        display: flex;
    }

    #chat-window-selection.is-collapsed .chat-thread-item {
        padding: 10px 6px;
        text-align: center;
    }

    #chat-window-selection.is-collapsed .thread-user-header {
        margin: 0;
        justify-content: center;
    }

    #chat-window-selection.is-collapsed .thread-user-name {
        font-size: 0.8rem;
        text-align: center;
        display: block;
        width: 100%;
    }

    #chat-window-selection.is-collapsed .thread-time,
    #chat-window-selection.is-collapsed .thread-last-msg,
    #chat-window-selection.is-collapsed .thread-badges {
        display: none !important;
    }

    /* =========================================================================
       右侧主容器：严格锁死高度，内部分为 顶栏 + 窗口2(消息可滚动) + 窗口3(输入框固定底端)
       ========================================================================= */
    .chat-workbench-main {
        flex: 1 1 0% !important;
        min-height: 0 !important;
        height: 100% !important;
        max-height: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        background: var(--bg-page);
        position: relative;
    }

    /* 对话顶部信息条 (固定在消息区上方，不随消息滚动) */
    .chat-header {
        padding: 12px 24px;
        background: var(--bg-card);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 56px;
        box-sizing: border-box;
        flex-shrink: 0 !important;
    }

    .chat-header-user-info {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .chat-header-name {
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .chat-header-session-id {
        font-size: 0.75rem;
        background: var(--bg-deep);
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        padding: 2px 8px;
        border-radius: 4px;
        font-family: 'Geist Mono', monospace, sans-serif;
    }

    .chat-header-right-meta {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .chat-header-meta {
        font-size: 0.8125rem;
        color: var(--text-muted);
        display: flex;
        gap: 14px;
        align-items: center;
    }

    .chat-refresh-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        color: var(--primary);
        background: var(--accent-badge);
        border: 1px solid rgba(34, 197, 94, 0.25);
        padding: 3px 8px;
        border-radius: 12px;
        font-family: 'Geist Mono', monospace, sans-serif;
    }

    .chat-refresh-pulse-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--primary);
        box-shadow: 0 0 6px var(--primary);
        animation: chat-pulse 2s infinite ease-in-out;
    }

    @keyframes chat-pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.85); }
    }

    /* =========================================================================
       窗口 2：显示消息的聊天窗口 (核心滚动容器：输入框在其外部！有新消息只在此窗口内滑动)
       ========================================================================= */
    #chat-window-messages {
        flex: 1 1 0% !important;
        min-height: 0 !important;
        height: auto !important;
        padding: 20px 28px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        background: var(--bg-page);
        overflow-y: scroll !important; /* 核心：独立纵向滑动槽 */
        overflow-x: hidden !important;
    }

    .chat-message-row {
        display: flex;
        flex-direction: column;
        max-width: 75%;
        flex-shrink: 0;
    }

    .chat-message-row.is-staff {
        align-self: flex-end;
        align-items: flex-end;
    }

    .chat-message-row.is-customer {
        align-self: flex-start;
        align-items: flex-start;
    }

    .chat-message-row.is-bot {
        align-self: flex-start;
        align-items: flex-start;
    }

    .message-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .message-sender-name {
        font-weight: 600;
    }

    .message-sender-name.staff {
        color: var(--primary);
    }

    .message-sender-name.customer {
        color: #60a5fa;
    }

    .message-sender-name.bot {
        color: var(--warning);
    }

    .message-bubble {
        padding: 12px 16px;
        font-size: 0.9375rem;
        line-height: 1.55;
        white-space: pre-wrap;
        word-break: break-word;
        box-shadow: var(--shadow-sm);
        width: fit-content;
        max-width: 100%;
        box-sizing: border-box;
    }

    .chat-message-row.is-staff .message-bubble {
        background: rgba(34, 197, 94, 0.12);
        border: 1px solid rgba(34, 197, 94, 0.3);
        color: var(--text-primary);
        border-radius: 14px 14px 2px 14px;
    }

    .chat-message-row.is-customer .message-bubble {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        border-radius: 14px 14px 14px 2px;
    }

    .chat-message-row.is-bot .message-bubble {
        background: var(--bg-raised);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        border-radius: 14px 14px 14px 2px;
        font-style: italic;
    }

    /* =========================================================================
       窗口 3：打字和发送消息的输入框 (永久固定在聊天窗口的最底下同一位置，绝不上移下移)
       ========================================================================= */
    #chat-window-input {
        flex-shrink: 0 !important;
        width: 100% !important;
        padding: 14px 24px;
        background: var(--bg-card) !important;
        border-top: 1px solid var(--border-color) !important;
        box-sizing: border-box;
        position: relative;
        z-index: 20;
    }

    .chat-reply-form {
        display: flex;
        gap: 12px;
        align-items: flex-end;
    }

    .chat-reply-input {
        flex: 1;
        background: var(--bg-input);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        padding: 10px 14px;
        font-size: 0.9375rem;
        color: var(--text-primary);
        resize: none;
        outline: none;
        min-height: 44px;
        max-height: 100px;
        font-family: inherit;
        line-height: 1.45;
        transition: border-color 0.2s;
    }

    .chat-reply-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 1px var(--primary);
    }

    .chat-reply-input::placeholder {
        color: var(--text-muted);
    }

    .chat-send-btn {
        background: var(--primary);
        color: #000000;
        border: none;
        border-radius: var(--radius-sm);
        padding: 0 22px;
        height: 44px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background 0.18s, transform 0.1s;
        flex-shrink: 0;
    }

    .chat-send-btn:hover {
        background: var(--primary-hover);
    }

    .chat-send-btn:active {
        transform: translateY(1px);
    }

    .chat-reply-hints {
        display: flex;
        justify-content: space-between;
        margin-top: 6px;
        font-size: 0.72rem;
        color: var(--text-muted);
    }

    /* 空会话状态 */
    .chat-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--text-muted);
        padding: 40px;
        text-align: center;
    }

    .chat-empty-icon {
        width: 60px;
        height: 60px;
        border-radius: 14px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        color: var(--primary);
    }
</style>
@endsection

@section('content')
<div class="chat-workbench-container">
    
    <!-- =========================================================================
         窗口 1：选择聊天对象的聊天选择窗口 (独立的滑动窗口，独立上下滑动)
         ========================================================================= -->
    <aside id="chat-window-selection" class="chat-window-selection" aria-label="Conversation list">
        <!-- 侧边栏顶部工具栏：标题、计数与收起/展开按钮 -->
        <div class="chat-sidebar-header">
            <div class="chat-sidebar-title-area">
                <h3 class="chat-sidebar-title">Inquiry Threads</h3>
                <span class="chat-badge-count">
                    {{ $sessions->count() }} active
                </span>
            </div>
            <button type="button" id="toggle-sidebar-btn" class="chat-sidebar-toggle-btn" title="Collapse list (show names only)" aria-label="Toggle sidebar">
                <svg id="toggle-sidebar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
        </div>

        <!-- 窗口 1 专属滑动槽列表 (独立上下滑动) -->
        <div id="selection-scroll-area" class="sliding-trough">
            @forelse($sessions as $sess)
                @php
                    $isActive = $sess->session_id === $activeSessionId;
                    $displayName = $sess->customer ? $sess->customer->name : ('Guest (' . substr($sess->session_id, 0, 8) . ')');
                    $avatarLetter = strtoupper(substr($displayName, 0, 1));
                @endphp
                <a href="{{ route('erp.chat.index', ['session_id' => $sess->session_id]) }}" 
                   class="chat-thread-item {{ $isActive ? 'is-active' : '' }}"
                   data-session-id="{{ $sess->session_id }}"
                   title="{{ $displayName }}">
                    
                    <!-- 缩小模式下的头像首字母 -->
                    <div class="collapsed-avatar">
                        {{ $avatarLetter }}
                    </div>

                    <div class="thread-user-header">
                        <span class="thread-user-name">
                            {{ $displayName }}
                        </span>
                        <span class="thread-time">
                            {{ $sess->last_activity ? $sess->last_activity->diffForHumans(null, true) : '' }}
                        </span>
                    </div>

                    <div class="thread-last-msg">
                        @if($sess->last_sender === 'Agent')
                            <span class="agent-prefix">Staff: </span>
                        @endif
                        <span class="msg-snippet">{{ $sess->last_message ?: 'New inquiry opened...' }}</span>
                    </div>

                    <div class="thread-badges">
                        @if($sess->customer)
                            <span class="badge-user-type registered">Registered</span>
                        @else
                            <span class="badge-user-type">Visitor</span>
                        @endif
                        <span class="badge-msg-count">{{ $sess->total_messages }} msgs</span>
                    </div>
                </a>
            @empty
                <div style="text-align: center; padding: 48px 16px; color: var(--text-muted);">
                    <div style="font-size: 1.75rem; margin-bottom: 8px;">💬</div>
                    <p style="font-size: 0.875rem; margin: 0; color: var(--text-secondary);">No customer inquiries yet.</p>
                    <p style="font-size: 0.75rem; margin-top: 4px;">Storefront chat messages will appear here in real time.</p>
                </div>
            @endforelse
        </div>
    </aside>

    <!-- 右侧主容器：包含顶部信息栏、窗口 2 (消息区域) 与 窗口 3 (输入框) -->
    <main class="chat-workbench-main">
        @if($activeSessionId)
            <!-- 对话顶部信息栏 (固定在顶部) -->
            <header class="chat-header">
                <div class="chat-header-user-info">
                    <h3 class="chat-header-name">
                        @if($activeCustomer)
                            {{ $activeCustomer->name }}
                        @else
                            Storefront Guest Visitor
                        @endif
                    </h3>
                    <span class="chat-header-session-id">
                        Session: {{ substr($activeSessionId, 0, 16) }}...
                    </span>
                </div>
                <div class="chat-header-right-meta">
                    <div class="chat-header-meta">
                        @if($activeCustomer)
                            <span>{{ $activeCustomer->email }}</span>
                            <span>•</span>
                            <span>{{ $activeCustomer->orders_count ?? 0 }} Orders</span>
                            <span>•</span>
                            <span>{{ $activeCustomer->support_tickets_count ?? 0 }} RMA Tickets</span>
                        @else
                            <span>Public storefront visitor inquiry</span>
                        @endif
                    </div>
                    <!-- 定时自动刷新状态指示 -->
                    <div class="chat-refresh-badge" title="Auto-refresh active">
                        <span class="chat-refresh-pulse-dot"></span>
                        <span>Live Sync</span>
                    </div>
                </div>
            </header>

            <!-- =========================================================================
                 窗口 2：显示消息的聊天窗口 (核心滑动窗口，不包括输入框，新消息只在此窗口内滑动)
                 ========================================================================= -->
            <section id="chat-window-messages" class="sliding-trough" aria-label="Messages stream">
                @forelse($activeMessages as $msg)
                    @php
                        $isStaff = $msg->sender_type === 'Agent';
                        $isBot = $msg->sender_type === 'Bot';
                    @endphp
                    <div class="chat-message-row {{ $isStaff ? 'is-staff' : ($isBot ? 'is-bot' : 'is-customer') }}" data-msg-id="{{ $msg->id }}">
                        <div class="message-meta">
                            @if($isStaff)
                                <span class="message-sender-name staff">Staff Specialist (You)</span>
                            @elseif($isBot)
                                <span class="message-sender-name bot">GOPC Assistant</span>
                            @else
                                <span class="message-sender-name customer">
                                    {{ $activeCustomer ? $activeCustomer->name : 'Customer' }}
                                </span>
                            @endif
                            <span>{{ $msg->created_at ? $msg->created_at->format('M d, g:i A') : '' }}</span>
                        </div>
                        <div class="message-bubble">{{ $msg->message }}</div>
                    </div>
                @empty
                    <div style="text-align: center; margin: auto; color: var(--text-muted); font-size: 0.9rem;">
                        No messages in this inquiry thread yet. Write a message below to start assisting.
                    </div>
                @endforelse
            </section>

            <!-- =========================================================================
                 窗口 3：打字和发送消息的输入框 (永远固定在聊天界面的最底下同一个地方)
                 ========================================================================= -->
            <footer id="chat-window-input" aria-label="Message composer">
                <form id="chat-reply-form" method="POST" action="{{ route('erp.chat.reply', ['sessionId' => $activeSessionId]) }}" class="chat-reply-form">
                    @csrf
                    <textarea id="chat-reply-input" name="message" rows="2" placeholder="Write an official customer support reply..." required class="chat-reply-input"></textarea>
                    <button type="submit" id="chat-submit-btn" class="chat-send-btn">
                        <span>Send</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </form>
                <div class="chat-reply-hints">
                    <span>Press <kbd style="background: var(--bg-deep); border: 1px solid var(--border-color); padding: 1px 4px; border-radius: 3px; font-family: monospace;">Enter</kbd> to send, <kbd style="background: var(--bg-deep); border: 1px solid var(--border-color); padding: 1px 4px; border-radius: 3px; font-family: monospace;">Shift+Enter</kbd> for new line</span>
                    <span>Composer locked at bottom</span>
                </div>
            </footer>
        @else
            <!-- 未选中会话时的空状态 -->
            <div class="chat-empty-state">
                <div class="chat-empty-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <h3 style="color: var(--text-primary); margin: 0 0 8px; font-size: 1.15rem; font-weight: 600;">No Thread Selected</h3>
                <p style="max-width: 440px; margin: 0; font-size: 0.875rem; line-height: 1.5; color: var(--text-secondary);">
                    Select a conversation thread from the queue on the left to inspect customer inquiry details and transmit real-time specialist replies.
                </p>
            </div>
        @endif
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectionWindow = document.getElementById('chat-window-selection');
        const toggleBtn = document.getElementById('toggle-sidebar-btn');
        const messagesWindow = document.getElementById('chat-window-messages');
        const replyForm = document.getElementById('chat-reply-form');
        const replyInput = document.getElementById('chat-reply-input');
        const activeSessionId = @json($activeSessionId);
        const historyUrl = activeSessionId ? "{{ route('erp.chat.history', ['sessionId' => ':session_id']) }}".replace(':session_id', activeSessionId) : null;

        // 1. 窗口 1：缩小与放大状态切换及记忆
        function setSidebarState(collapsed) {
            if (!selectionWindow || !toggleBtn) return;
            if (collapsed) {
                selectionWindow.classList.add('is-collapsed');
                toggleBtn.title = "Expand list (show details)";
                toggleBtn.setAttribute('aria-label', 'Expand list');
                toggleBtn.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                `;
            } else {
                selectionWindow.classList.remove('is-collapsed');
                toggleBtn.title = "Collapse list (names only)";
                toggleBtn.setAttribute('aria-label', 'Collapse list');
                toggleBtn.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                `;
            }
        }

        const savedCollapsedState = localStorage.getItem('erp_chat_sidebar_collapsed') === 'true';
        setSidebarState(savedCollapsedState);

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                const isNowCollapsed = !selectionWindow.classList.contains('is-collapsed');
                setSidebarState(isNowCollapsed);
                localStorage.setItem('erp_chat_sidebar_collapsed', isNowCollapsed);
            });
        }

        // 2. 窗口 2：当有新消息时，只在聊天窗口内部自动滑到最下面（输入框不动）
        function scrollChatToBottom(smooth = true) {
            if (!messagesWindow) return;
            if (smooth) {
                messagesWindow.scrollTo({
                    top: messagesWindow.scrollHeight,
                    behavior: 'smooth'
                });
            } else {
                messagesWindow.scrollTop = messagesWindow.scrollHeight;
            }
        }

        // 首次打开自动滑到底部
        scrollChatToBottom(false);

        // 3. 窗口 3：消息输入框键盘快捷键与防重即时发送
        let isSubmitting = false;
        const submitBtn = document.getElementById('chat-submit-btn');

        if (replyForm && replyInput) {
            replyInput.addEventListener('keydown', function (e) {
                // 如果处于输入法输入拼音过程中，不触发提交
                if (e.isComposing || e.keyCode === 229) {
                    return;
                }

                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (!isSubmitting) {
                        replyForm.dispatchEvent(new Event('submit', { cancelable: true }));
                    }
                }
            });

            replyForm.addEventListener('submit', function (e) {
                e.preventDefault();

                // 防重锁：防止短时间内重复提交发送两次
                if (isSubmitting) {
                    return;
                }

                const messageText = replyInput.value.trim();
                if (!messageText) {
                    return;
                }

                isSubmitting = true;
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.6';
                    submitBtn.style.pointerEvents = 'none';
                }

                const url = replyForm.getAttribute('action');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || replyForm.querySelector('input[name="_token"]')?.value;

                // 生成唯一的客户端临时标记，避免与轮询冲突而产生两条
                const tempId = 'temp-' + Date.now() + '-' + Math.random().toString(36).substring(2, 7);

                // 立即在窗口 2 渲染专员消息气泡
                const tempRow = appendMessageBubble({
                    id: tempId,
                    sender_type: 'Agent',
                    message: messageText,
                    time: 'Just now'
                });

                replyInput.value = '';
                replyInput.style.height = 'auto';

                // 有新消息时：只在聊天窗口内部滑动到最下面，输入框固定不动
                scrollChatToBottom(true);

                // 更新窗口 1 对应的最新消息预览
                updateSessionSnippet(activeSessionId, 'Staff: ' + messageText, 'Just now');

                // AJAX 提交到后端
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ message: messageText })
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Server returned ' + res.status);
                    }
                    return res.json();
                })
                .then(data => {
                    // 后端成功保存后，将真实的数据库 ID 赋给刚刚渲染的气泡
                    if (data && data.message && data.message.id && tempRow) {
                        tempRow.setAttribute('data-msg-id', data.message.id);
                        const timeSpan = tempRow.querySelector('.message-meta span:last-child');
                        if (timeSpan && data.message.time) {
                            timeSpan.textContent = data.message.time;
                        }
                    }
                })
                .catch(err => {
                    console.error('Failed to post reply via AJAX:', err);
                    if (tempRow) {
                        tempRow.style.opacity = '0.6';
                        tempRow.title = 'Failed to send. Please check your network.';
                    }
                })
                .finally(() => {
                    // 释放提交锁并恢复按钮
                    setTimeout(() => {
                        isSubmitting = false;
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.style.opacity = '1';
                            submitBtn.style.pointerEvents = 'auto';
                        }
                    }, 400);
                });
            });
        }

        // 4. 追加消息气泡助手
        function appendMessageBubble(msg) {
            if (!messagesWindow) return null;
            const isStaff = msg.sender_type === 'Agent';
            const isBot = msg.sender_type === 'Bot';
            const customerName = @json($activeCustomer ? $activeCustomer->name : 'Customer');

            const row = document.createElement('div');
            row.className = `chat-message-row ${isStaff ? 'is-staff' : (isBot ? 'is-bot' : 'is-customer')}`;
            if (msg.id) {
                row.setAttribute('data-msg-id', msg.id);
            }

            let senderLabel = '';
            if (isStaff) {
                senderLabel = '<span class="message-sender-name staff">Staff Specialist (You)</span>';
            } else if (isBot) {
                senderLabel = '<span class="message-sender-name bot">GOPC Assistant</span>';
            } else {
                senderLabel = `<span class="message-sender-name customer">${escapeHtml(customerName)}</span>`;
            }

            row.innerHTML = `
                <div class="message-meta">
                    ${senderLabel}
                    <span>${msg.time || ''}</span>
                </div>
                <div class="message-bubble">${escapeHtml(msg.message)}</div>
            `;

            messagesWindow.appendChild(row);
            return row;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function updateSessionSnippet(sessionId, text, timeText) {
            const item = document.querySelector(`.chat-thread-item[data-session-id="${sessionId}"]`);
            if (!item) return;
            const snippet = item.querySelector('.msg-snippet');
            if (snippet) snippet.textContent = text;
            const timeEl = item.querySelector('.thread-time');
            if (timeEl && timeText) timeEl.textContent = timeText;
        }

        // 5. 聊天框一定时间后会自动刷新 (每3秒定时拉取新消息，带双重去重机制)
        if (historyUrl) {
            setInterval(function () {
                fetch(historyUrl, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (!data || !Array.isArray(data.messages)) return;
                    let hasNewMessage = false;
                    let latestMsg = null;

                    data.messages.forEach(msg => {
                        // 1. 精确匹配消息 ID
                        let existing = messagesWindow.querySelector(`[data-msg-id="${msg.id}"]`);

                        // 2. 匹配临时发送的专员气泡，防止乐观渲染与轮询数据重复叠加产生两条
                        if (!existing && msg.sender_type === 'Agent') {
                            const pendingStaffBubbles = messagesWindow.querySelectorAll('.chat-message-row.is-staff[data-msg-id^="temp-"]');
                            for (let row of pendingStaffBubbles) {
                                const bubbleText = row.querySelector('.message-bubble')?.textContent || '';
                                if (bubbleText === msg.message) {
                                    row.setAttribute('data-msg-id', msg.id);
                                    const timeSpan = row.querySelector('.message-meta span:last-child');
                                    if (timeSpan && msg.time) {
                                        timeSpan.textContent = msg.time;
                                    }
                                    existing = row;
                                    break;
                                }
                            }
                        }

                        if (!existing) {
                            appendMessageBubble(msg);
                            hasNewMessage = true;
                            latestMsg = msg;
                        }
                    });

                    // 当有真正的新客户消息时，仅聊天窗口自身平滑滑到最下面，输入框纹丝不动
                    if (hasNewMessage) {
                        scrollChatToBottom(true);
                        if (latestMsg) {
                            const prefix = latestMsg.sender_type === 'Agent' ? 'Staff: ' : '';
                            updateSessionSnippet(activeSessionId, prefix + latestMsg.message, latestMsg.time);
                        }
                    }
                })
                .catch(() => {});
            }, 3000);
        }
    });
</script>
@endsection
