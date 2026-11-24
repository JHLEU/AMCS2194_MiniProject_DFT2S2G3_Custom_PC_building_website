{{-- Storefront Floating Live Chat Widget --}}
<div id="pc-live-chat-wrapper" style="position: fixed; bottom: 24px; right: 24px; z-index: 9999; font-family: inherit;">
    <!-- Launcher Button -->
    <button id="chat-launcher-btn" type="button" aria-label="Open live chat" style="display: flex; align-items: center; gap: 10px; background: #0f172a; color: #ffffff; border: 1px solid rgba(34, 197, 94, 0.4); border-radius: 50px; padding: 12px 20px; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6); transition: all 0.25s ease;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary, #22c55e)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span style="letter-spacing: 0.02em;">Live Support</span>
        <span style="display: inline-block; width: 8px; height: 8px; background-color: var(--primary, #22c55e); border-radius: 50%; box-shadow: 0 0 8px var(--primary, #22c55e);"></span>
    </button>

    <!-- Chat Box Window -->
    <div id="chat-widget-window" style="display: none; position: absolute; bottom: 64px; right: 0; width: 360px; max-width: calc(100vw - 32px); height: 500px; max-height: calc(100vh - 120px); background: #0b0f19; border: 1px solid var(--border-color, rgba(255, 255, 255, 0.12)); border-radius: 14px; box-shadow: 0 24px 50px rgba(0, 0, 0, 0.7); overflow: hidden; flex-direction: column;">
        <!-- Top Green Accent Line -->
        <div style="height: 2px; width: 100%; background: linear-gradient(90deg, transparent, var(--primary, #22c55e), transparent);"></div>

        <!-- Header -->
        <div style="background: rgba(18, 24, 38, 0.95); padding: 14px 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="position: relative;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.3); display: flex; align-items: center; justify-content: center; color: var(--primary, #22c55e);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <span style="position: absolute; bottom: -2px; right: -2px; width: 9px; height: 9px; background-color: var(--primary, #22c55e); border: 2px solid #0b0f19; border-radius: 50%;"></span>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 0.9rem; color: #fff; font-weight: 700; letter-spacing: -0.3px;">Hardware Specialist</h4>
                    <p style="margin: 2px 0 0; font-size: 0.74rem; color: var(--text-muted, #94a3b8); font-family: monospace;">STATUS: ONLINE</p>
                </div>
            </div>
            <button id="chat-close-btn" type="button" aria-label="Close live chat" style="background: none; border: none; color: #94a3b8; font-size: 1.4rem; cursor: pointer; padding: 2px 6px; line-height: 1; transition: color 0.2s;">
                &times;
            </button>
        </div>

        <!-- Chat History Stream -->
        <div id="chat-messages-container" style="flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; background: #070a10;">
            <!-- Initial Greeting -->
            <div class="chat-msg chat-msg-agent" style="align-self: flex-start; max-width: 85%; background: #131b2e; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px 10px 10px 2px; padding: 10px 14px; color: #e2e8f0; font-size: 0.85rem; line-height: 1.45;">
                <div style="font-size: 0.7rem; color: var(--primary, #22c55e); font-weight: 600; margin-bottom: 3px; font-family: monospace; text-transform: uppercase;">Service Desk</div>
                <div>Hello! Welcome to GOPC. How can we assist you today? Feel free to ask about custom PC compatibility, part recommendations, or order tracking.</div>
            </div>
        </div>

        <!-- Message Input Footer -->
        <form id="chat-widget-form" style="padding: 12px 14px; background: rgba(18, 24, 38, 0.95); border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; gap: 8px;">
            <input type="text" id="chat-input-text" placeholder="Type your hardware inquiry..." maxlength="1000" autocomplete="off" style="flex: 1; background: #090d16; border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 8px; padding: 9px 12px; font-size: 0.85rem; color: #f8fafc; outline: none; transition: border-color 0.2s;" />
            <button type="submit" id="chat-submit-btn" style="background: var(--primary, #22c55e); color: #000000; border: none; border-radius: 8px; padding: 0 16px; font-size: 0.85rem; font-weight: 700; cursor: pointer; transition: opacity 0.2s; display: flex; align-items: center; justify-content: center;">
                <span>Send</span>
            </button>
        </form>
    </div>
</div>
