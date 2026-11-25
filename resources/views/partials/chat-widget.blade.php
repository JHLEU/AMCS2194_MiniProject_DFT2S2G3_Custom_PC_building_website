<div id="pc-live-chat-wrapper">
    <button type="button" id="chat-launcher-btn" class="btn btn-primary"
            aria-controls="chat-widget-window" aria-expanded="false">Live Support</button>

    <section id="chat-widget-window" hidden aria-labelledby="chat-widget-title"
             data-history-url="{{ route('shopping-chat.history') }}"
             data-send-url="{{ route('shopping-chat.send') }}"
             data-handoff-url="{{ route('shopping-chat.handoff') }}">
        <header>
            <div>
                <h2 id="chat-widget-title">GOPC Live Support</h2>
                <p id="chat-widget-channel">AI shopping assistant</p>
            </div>
            <button type="button" id="chat-close-btn" class="btn btn-outline btn-sm">Close</button>
        </header>

        <div id="shopping-assistant-messages" role="log" aria-label="Conversation" tabindex="0"></div>
        <p id="shopping-assistant-status" role="status"></p>

        <form id="shopping-assistant-form">
            <label for="shopping-assistant-message">Your message</label>
            <div>
                <input id="shopping-assistant-message" name="message" maxlength="1000" required
                       placeholder="Is the RTX 4070 in stock?" autocomplete="off">
                <button type="submit" class="btn btn-primary">Send</button>
            </div>
        </form>
        <button type="button" id="chat-handoff-btn" class="btn btn-outline btn-sm">Talk to a specialist</button>
    </section>
</div>
