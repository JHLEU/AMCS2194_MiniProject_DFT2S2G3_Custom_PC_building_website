(() => {
    const launcher = document.getElementById('chat-launcher-btn');
    const panel = document.getElementById('chat-widget-window');
    const closeButton = document.getElementById('chat-close-btn');
    const handoffButton = document.getElementById('chat-handoff-btn');
    const channel = document.getElementById('chat-widget-channel');
    const form = document.getElementById('shopping-assistant-form');
    const input = document.getElementById('shopping-assistant-message');
    const messages = document.getElementById('shopping-assistant-messages');
    const status = document.getElementById('shopping-assistant-status');
    const send = form.querySelector('button[type="submit"]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    let busy = false;
    let polling = false;
    let humanSupport = false;
    let pollTimer;
    let currentHistory;
    let revision = 0;

    async function request(url, body) {
        const response = await fetch(url, {
            method: body === undefined ? 'GET' : 'POST',
            body: body === undefined ? undefined : JSON.stringify(body),
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        if (!response.ok) {
            if (response.status === 419) {
                throw new Error('Your session expired. Refresh the page and try again.');
            }
            if (response.status === 429) {
                throw new Error('Too many requests. Please wait a minute and try again.');
            }
            if (response.status === 503 && url === panel.dataset.sendUrl) {
                throw new Error(humanSupport
                    ? 'Support is temporarily unavailable. Please try again.'
                    : 'The AI assistant is unavailable. Select "Talk to a specialist" to continue.');
            }
            throw new Error('Unable to update the conversation. Please try again.');
        }

        return response.json();
    }

    function appendMessage(message, senderType) {
        const row = document.createElement('p');
        row.toggleAttribute('data-customer', senderType === 'Customer');
        if (senderType === 'Agent') {
            row.setAttribute('data-agent', '');
        }
        row.textContent = message;
        messages.append(row);
        messages.scrollTop = messages.scrollHeight;
    }

    function updateConversation(data) {
        humanSupport = data.human_support;
        channel.textContent = humanSupport ? 'Human support' : 'AI shopping assistant';
        handoffButton.hidden = humanSupport;
        input.placeholder = humanSupport ? 'Write to our support team' : 'Is the RTX 4070 in stock?';

        const snapshot = JSON.stringify(data.messages);
        if (snapshot !== currentHistory) {
            currentHistory = snapshot;
            messages.replaceChildren();
            if (!data.messages.length) {
                appendMessage('Ask me about GOPC components, specifications, or current stock.', 'Bot');
            }
            data.messages.forEach(({ message, sender_type }) => appendMessage(message, sender_type));
        }

        if (humanSupport && !panel.hidden && !pollTimer) {
            pollTimer = setInterval(refresh, 5000);
        }
    }

    async function refresh() {
        if (busy || polling || panel.hidden) return;

        polling = true;
        const startedAt = revision;
        try {
            const data = await request(panel.dataset.historyUrl);
            if (!busy && !panel.hidden && startedAt === revision) updateConversation(data);
        } catch (error) {
            if (!busy && !panel.hidden) status.textContent = error.message;
        } finally {
            polling = false;
        }
    }

    async function run(action, pending) {
        if (busy) return;
        busy = true;
        revision++;
        send.disabled = true;
        handoffButton.disabled = true;
        status.textContent = pending;
        try {
            await action();
            status.textContent = '';
        } catch (error) {
            status.textContent = error.message;
        } finally {
            busy = false;
            send.disabled = false;
            handoffButton.disabled = false;
        }
    }

    function close() {
        panel.hidden = true;
        launcher.setAttribute('aria-expanded', 'false');
        clearInterval(pollTimer);
        pollTimer = undefined;
        launcher.focus();
    }

    launcher.addEventListener('click', () => {
        if (!panel.hidden) {
            close();
            return;
        }

        panel.hidden = false;
        launcher.setAttribute('aria-expanded', 'true');
        input.focus();
        run(async () => updateConversation(await request(panel.dataset.historyUrl)), 'Loading conversation...');
    });
    closeButton.addEventListener('click', close);
    panel.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
    });

    handoffButton.addEventListener('click', () => {
        run(async () => {
            updateConversation(await request(panel.dataset.handoffUrl, {}));
            if (!panel.hidden) input.focus();
        }, 'Requesting human support...');
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const message = input.value.trim();
        if (!message) return;

        run(async () => {
            const data = await request(panel.dataset.sendUrl, { message });
            if (data.messages) {
                updateConversation(data);
            } else {
                appendMessage(message, 'Customer');
                appendMessage(data.reply, 'Bot');
            }
            if (input.value.trim() === message) input.value = '';
        }, humanSupport ? 'Sending to support...' : 'Checking the catalog...');
    });
})();
