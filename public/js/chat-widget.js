/**
 * Customer Support Floating Live Chat Widget (jQuery)
 */
$(document).ready(function () {
    const $launcherBtn = $('#chat-launcher-btn');
    const $chatWindow = $('#chat-widget-window');
    const $closeBtn = $('#chat-close-btn');
    const $messagesContainer = $('#chat-messages-container');
    const $chatForm = $('#chat-widget-form');
    const $chatInput = $('#chat-input-text');
    const $submitBtn = $('#chat-submit-btn');
    const $channels = $chatWindow.find('[data-chat-panel]');
    const $panels = $chatWindow.find('.live-support-panel');
    let activePanel = 'human-support-panel';

    const STORAGE_KEY = 'pc_chat_session_id';
    let pollInterval = null;
    let knownMessageIds = new Set();

    // Get CSRF token
    const csrfToken = $('meta[name="csrf-token"]').attr('content') || '';

    function getSessionId() {
        return localStorage.getItem(STORAGE_KEY) || '';
    }

    function setSessionId(id) {
        if (id) {
            localStorage.setItem(STORAGE_KEY, id);
        }
    }

    function scrollToBottom() {
        $messagesContainer.stop().animate({
            scrollTop: $messagesContainer.prop('scrollHeight')
        }, 200);
    }

    function formatTime(isoString) {
        if (!isoString) return '';
        try {
            const d = new Date(isoString);
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        } catch (e) {
            return '';
        }
    }

    function renderMessage(msg) {
        if (knownMessageIds.has(msg.id)) {
            return;
        }
        knownMessageIds.add(msg.id);

        const isCustomer = msg.is_customer || msg.sender_type === 'Customer';
        const $msgDiv = $('<div>').addClass('chat-msg');

        if (isCustomer) {
            $msgDiv.css({
                'align-self': 'flex-end',
                'max-width': '82%',
                'background': 'linear-gradient(135deg, #2563eb, #1d4ed8)',
                'border-radius': '12px 12px 2px 12px',
                'padding': '10px 14px',
                'color': '#ffffff',
                'font-size': '0.88rem',
                'line-height': '1.45',
                'box-shadow': '0 2px 8px rgba(37, 99, 235, 0.25)'
            });

            const $textDiv = $('<div>').text(msg.message);
            const $timeDiv = $('<div>').css({
                'font-size': '0.70rem',
                'color': 'rgba(255, 255, 255, 0.75)',
                'text-align': 'right',
                'margin-top': '4px'
            }).text(msg.time || formatTime(msg.created_at));

            $msgDiv.append($textDiv, $timeDiv);
        } else {
            // Support Agent
            $msgDiv.css({
                'align-self': 'flex-start',
                'max-width': '82%',
                'background': '#1e293b',
                'border': '1px solid rgba(255, 255, 255, 0.08)',
                'border-radius': '12px 12px 12px 2px',
                'padding': '10px 14px',
                'color': '#e2e8f0',
                'font-size': '0.88rem',
                'line-height': '1.45'
            });

            const $badgeDiv = $('<div>').css({
                'font-size': '0.72rem',
                'color': '#60a5fa',
                'font-weight': '600',
                'margin-bottom': '3px'
            }).text('👨‍💼 Support Specialist');

            const $textDiv = $('<div>').text(msg.message);
            const $timeDiv = $('<div>').css({
                'font-size': '0.70rem',
                'color': '#94a3b8',
                'margin-top': '4px'
            }).text(msg.time || formatTime(msg.created_at));

            $msgDiv.append($badgeDiv, $textDiv, $timeDiv);
        }

        $messagesContainer.append($msgDiv);
        scrollToBottom();
    }

    function fetchMessages() {
        const sessionId = getSessionId();
        if (!sessionId) {
            return;
        }

        $.ajax({
            url: '/api/chat/messages',
            method: 'GET',
            data: { session_id: sessionId },
            dataType: 'json',
            success: function (response) {
                if (response && response.messages && Array.isArray(response.messages)) {
                    response.messages.forEach(renderMessage);
                }
            },
            error: function (xhr) {
                console.warn('Could not poll live chat messages:', xhr);
            }
        });
    }

    function activateChannel() {
        if (activePanel === 'shopping-assistant-panel') {
            stopPolling();
            document.getElementById(activePanel).dispatchEvent(new Event('support:open'));
            return;
        }

        fetchMessages();
        scrollToBottom();

        if (!pollInterval) {
            pollInterval = setInterval(fetchMessages, 4000);
        }
    }

    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    function openChat() {
        $chatWindow.css('display', 'flex');
        $launcherBtn.attr('aria-expanded', 'true');
        activateChannel();
        $channels.filter('[aria-pressed="true"]').focus();
    }

    function closeChat() {
        $chatWindow.hide();
        stopPolling();
        $launcherBtn.attr('aria-expanded', 'false').focus();
    }

    $channels.on('click', function () {
        const nextPanel = this.dataset.chatPanel;
        if (nextPanel === activePanel) return;

        activePanel = nextPanel;
        $channels.attr('aria-pressed', 'false');
        $(this).attr('aria-pressed', 'true');
        $panels.each(function () {
            this.hidden = this.id !== activePanel;
        });
        activateChannel();
    });

    $chatWindow.on('keydown', function (event) {
        if (event.key === 'Escape') closeChat();
    });

    $launcherBtn.on('click', function () {
        if ($chatWindow.is(':visible')) {
            closeChat();
        } else {
            openChat();
        }
    });

    $closeBtn.on('click', function () {
        closeChat();
    });

    $chatForm.on('submit', function (e) {
        e.preventDefault();
        const rawText = $chatInput.val();
        const trimmed = (rawText || '').trim();

        if (!trimmed) {
            return;
        }

        $submitBtn.prop('disabled', true).text('Sending...');

        const sessionId = getSessionId();

        $.ajax({
            url: '/api/chat/send',
            method: 'POST',
            data: {
                message: trimmed,
                session_id: sessionId
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            dataType: 'json',
            success: function (response) {
                $chatInput.val('');
                if (response.session_id) {
                    setSessionId(response.session_id);
                }
                if (response.messages && Array.isArray(response.messages)) {
                    response.messages.forEach(renderMessage);
                }
            },
            error: function (xhr) {
                alert('Failed to send message. Please try again.');
            },
            complete: function () {
                $submitBtn.prop('disabled', false).text('Send');
                if ($chatWindow.is(':visible') && activePanel === 'human-support-panel') {
                    $chatInput.focus();
                }
            }
        });
    });
});
