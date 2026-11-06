{{-- Storefront Floating Live Chat Widget --}}
<div id="pc-live-chat-wrapper" style="position: fixed; bottom: 24px; right: 24px; z-index: 9999; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <!-- Launcher Button -->
    <button id="chat-launcher-btn" type="button" aria-label="Open live chat" style="display: flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #ffffff; border: none; border-radius: 50px; padding: 14px 22px; font-size: 0.95rem; font-weight: 600; cursor: pointer; box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4); transition: all 0.25s ease;">
        <span style="font-size: 1.25rem;">💬</span>
        <span>Live Support</span>
        <span style="display: inline-block; width: 9px; height: 9px; background-color: #10b981; border-radius: 50%; box-shadow: 0 0 8px #10b981;"></span>
    </button>

    <!-- Chat Box Window -->
    <div id="chat-widget-window" style="display: none; position: absolute; bottom: 64px; right: 0; width: 360px; max-width: calc(100vw - 32px); height: 500px; max-height: calc(100vh - 120px); background: #0f172a; border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 16px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6); overflow: hidden; flex-direction: column;">
        <!-- Header -->
        <div style="background: #1e293b; padding: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="position: relative;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        👨‍💼
                    </div>
                    <span style="position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; background-color: #10b981; border: 2px solid #1e293b; border-radius: 50%;"></span>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 0.95rem; color: #f8fafc; font-weight: 600;">Customer Support</h4>
                    <p style="margin: 2px 0 0; font-size: 0.75rem; color: #94a3b8;">Online | Ready to assist you</p>
                </div>
            </div>
            <button id="chat-close-btn" type="button" aria-label="Close live chat" style="background: none; border: none; color: #94a3b8; font-size: 1.25rem; cursor: pointer; padding: 4px; line-height: 1; transition: color 0.2s;">
                ✕
            </button>
        </div>

        <!-- Chat History Stream -->
        <div id="chat-messages-container" style="flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; background: #0b0f19;">
            <!-- Initial Greeting -->
            <div class="chat-msg chat-msg-agent" style="align-self: flex-start; max-width: 82%; background: #1e293b; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px 12px 12px 2px; padding: 10px 14px; color: #e2e8f0; font-size: 0.88rem; line-height: 1.45;">
                <div style="font-size: 0.72rem; color: #60a5fa; font-weight: 600; margin-bottom: 3px;">Support Specialist</div>
                <div>Hello! 👋 How can we assist you today? Feel free to ask about custom PC compatibility, build advice, or order status.</div>
            </div>
        </div>

        <!-- Message Input Footer -->
        <form id="chat-widget-form" style="padding: 12px 16px; background: #1e293b; border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; gap: 8px;">
            <input type="text" id="chat-input-text" placeholder="Type a message..." maxlength="1000" autocomplete="off" style="flex: 1; background: #0f172a; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; padding: 10px 14px; font-size: 0.88rem; color: #f8fafc; outline: none; transition: border-color 0.2s;" />
            <button type="submit" id="chat-submit-btn" style="background: #2563eb; color: #ffffff; border: none; border-radius: 8px; padding: 0 16px; font-size: 0.88rem; font-weight: 600; cursor: pointer; transition: background-color 0.2s; display: flex; align-items: center; justify-content: center;">
                <span>Send</span>
            </button>
        </form>
    </div>
</div>
