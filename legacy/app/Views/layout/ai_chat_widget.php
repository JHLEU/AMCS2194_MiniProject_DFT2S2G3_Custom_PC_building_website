<?php
/**
 * Global Floating AI Chat Assistant Widget
 */
$sessionToken = $_SESSION['chat_session_token'] ?? null;
if (!$sessionToken) {
    $sessionToken = 'sess_' . bin2hex(random_bytes(8));
    $_SESSION['chat_session_token'] = $sessionToken;
}
?>
<!-- Floating Chat Widget Styles & Markup -->
<style>
  .gopc-chat-fab {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ff334b 0%, #ff5268 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(255, 51, 75, 0.45);
    z-index: 9999;
    transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: none;
    outline: none;
  }
  .gopc-chat-fab:hover {
    transform: scale(1.08) translateY(-2px);
    box-shadow: 0 10px 28px rgba(255, 51, 75, 0.6);
  }
  .gopc-chat-fab-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 13px;
    height: 13px;
    background: #10b981;
    border: 2px solid #0a0b0e;
    border-radius: 50%;
  }

  .gopc-chat-window {
    position: fixed;
    bottom: 95px;
    right: 25px;
    width: 380px;
    height: 540px;
    background: #12141a;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 16px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.7), 0 0 20px rgba(255, 51, 75, 0.2);
    display: none;
    flex-direction: column;
    z-index: 9999;
    overflow: hidden;
    animation: gopcSlideUp 0.22s ease-out;
  }
  @keyframes gopcSlideUp {
    from { opacity: 0; transform: translateY(15px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
  }

  @media (max-width: 480px) {
    .gopc-chat-window {
      right: 10px;
      bottom: 80px;
      width: calc(100vw - 20px);
      height: 75vh;
    }
  }

  .gopc-chat-header {
    background: #161922;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255,255,255,0.08);
  }
  .gopc-chat-header-info {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .gopc-chat-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
  }
  .gopc-chat-title {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .gopc-chat-status {
    font-size: 11px;
    color: #10b981;
    display: flex;
    align-items: center;
    gap: 4px;
  }
  .gopc-chat-status::before {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    background: #10b981;
    border-radius: 50%;
  }

  .gopc-chat-actions button {
    background: none;
    border: none;
    color: #8c93a0;
    cursor: pointer;
    font-size: 16px;
    padding: 4px 6px;
    border-radius: 4px;
    transition: color 0.15s;
  }
  .gopc-chat-actions button:hover {
    color: #fff;
    background: rgba(255,255,255,0.05);
  }

  .gopc-chat-body {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: #0d0f14;
  }
  .gopc-chat-body::-webkit-scrollbar {
    width: 5px;
  }
  .gopc-chat-body::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.1);
    border-radius: 3px;
  }

  .gopc-msg {
    max-width: 85%;
    padding: 10px 14px;
    border-radius: 12px;
    font-size: 13px;
    line-height: 1.5;
    word-break: break-word;
    position: relative;
  }
  .gopc-msg.bot {
    align-self: flex-start;
    background: #161922;
    border: 1px solid rgba(255,255,255,0.08);
    color: #e2e8f0;
    border-bottom-left-radius: 2px;
  }
  .gopc-msg.bot a {
    color: #00f2fe;
    text-decoration: underline;
  }
  .gopc-msg.user {
    align-self: flex-end;
    background: #ff334b;
    color: #fff;
    border-bottom-right-radius: 2px;
    box-shadow: 0 2px 10px rgba(255,51,75,0.3);
  }
  .gopc-msg-time {
    font-size: 10px;
    opacity: 0.6;
    margin-top: 4px;
    display: block;
    text-align: right;
  }

  .gopc-quick-chips {
    padding: 8px 12px;
    background: #12141a;
    border-top: 1px solid rgba(255,255,255,0.05);
    display: flex;
    gap: 6px;
    overflow-x: auto;
    white-space: nowrap;
  }
  .gopc-quick-chips::-webkit-scrollbar {
    display: none;
  }
  .gopc-chip {
    background: #161922;
    border: 1px solid rgba(255,255,255,0.1);
    color: #8c93a0;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.15s;
  }
  .gopc-chip:hover {
    border-color: #ff334b;
    color: #fff;
    background: rgba(255,51,75,0.1);
  }

  .gopc-chat-footer {
    background: #161922;
    padding: 10px 12px;
    border-top: 1px solid rgba(255,255,255,0.08);
    display: flex;
    gap: 8px;
    align-items: center;
  }
  .gopc-chat-input {
    flex: 1;
    background: #0d0f14;
    border: 1px solid rgba(255,255,255,0.1);
    color: #fff;
    padding: 9px 12px;
    border-radius: 8px;
    font-size: 13px;
    outline: none;
    font-family: inherit;
  }
  .gopc-chat-input:focus {
    border-color: #ff334b;
  }
  .gopc-chat-send {
    background: #ff334b;
    border: none;
    color: #fff;
    padding: 9px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s;
  }
  .gopc-chat-send:hover {
    background: #e0243a;
  }
</style>

<!-- Floating Button -->
<button class="gopc-chat-fab" id="gopcChatFab" onclick="toggleGopcChat()" title="咨询 AI 硬件专家">
  <span id="gopcChatFabIcon">💬</span>
  <span class="gopc-chat-fab-badge"></span>
</button>

<!-- Chat Window -->
<div class="gopc-chat-window" id="gopcChatWindow">
  <div class="gopc-chat-header">
    <div class="gopc-chat-header-info">
      <div class="gopc-chat-avatar">🤖</div>
      <div>
        <div class="gopc-chat-title">GOPC 硬件 AI 专家</div>
        <div class="gopc-chat-status">在线 | 实时算力与装机建议</div>
      </div>
    </div>
    <div class="gopc-chat-actions">
      <a href="index.php?page=chat" title="进入全屏对话模式" style="color: #8c93a0; text-decoration: none; padding: 4px 6px; font-size: 14px;">⛶</a>
      <button onclick="clearGopcChatHistory()" title="清空对话记录 (Delete/Clear)">🗑️</button>
      <button onclick="toggleGopcChat()" title="关闭窗口">✕</button>
    </div>
  </div>

  <div class="gopc-chat-body" id="gopcChatBody">
    <div class="gopc-msg bot">
      您好！我是 GOPC 智能装机顾问 🤖<br>
      我可以帮您解答配件兼容性、预算推荐、功耗测算，或查询订单进度！
      <span class="gopc-msg-time"><?= date('H:i') ?></span>
    </div>
  </div>

  <div class="gopc-quick-chips">
    <button class="gopc-chip" onclick="quickSendChat('⚡ 推荐 RM 4000 的电竞配置')">⚡ RM 4000 推荐</button>
    <button class="gopc-chip" onclick="quickSendChat('🔥 7800X3D 配 4080S 怎么样？')">🔥 7800X3D + 4080S</button>
    <button class="gopc-chip" onclick="quickSendChat('🔍 帮我查询订单 ORD-20261101-8891')">🔍 查询订单</button>
    <button class="gopc-chip" onclick="quickSendChat('💳 支持哪些付款方式？')">💳 支付说明</button>
  </div>

  <form class="gopc-chat-footer" onsubmit="submitGopcChat(event)">
    <input type="text" id="gopcChatInput" class="gopc-chat-input" placeholder="输入问题或订单号..." autocomplete="off">
    <button type="submit" class="gopc-chat-send">发送</button>
  </form>
</div>

<script>
  let gopcChatOpen = false;
  const gopcSessionToken = '<?= htmlspecialchars($sessionToken) ?>';

  function toggleGopcChat() {
    const win = document.getElementById('gopcChatWindow');
    const fabIcon = document.getElementById('gopcChatFabIcon');
    gopcChatOpen = !gopcChatOpen;
    if (gopcChatOpen) {
      win.style.display = 'flex';
      fabIcon.textContent = '✕';
      document.getElementById('gopcChatInput').focus();
      loadChatHistory();
    } else {
      win.style.display = 'none';
      fabIcon.textContent = '💬';
    }
  }

  function appendChatMsg(sender, text, time) {
    const body = document.getElementById('gopcChatBody');
    const div = document.createElement('div');
    div.className = 'gopc-msg ' + (sender === 'User' ? 'user' : 'bot');
    const timeStr = time || new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    // Basic markdown conversion for bold and line breaks
    let formatted = text
      .replace(/\n/g, '<br>')
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
      .replace(/`([^`]+)`/g, '<code style="background:rgba(255,255,255,0.1);padding:2px 4px;border-radius:3px;">$1</code>');
    div.innerHTML = formatted + `<span class="gopc-msg-time">${timeStr}</span>`;
    body.appendChild(div);
    body.scrollTop = body.scrollHeight;
  }

  function quickSendChat(text) {
    document.getElementById('gopcChatInput').value = text;
    submitGopcChat(new Event('submit'));
  }

  async function submitGopcChat(e) {
    if (e) e.preventDefault();
    const input = document.getElementById('gopcChatInput');
    const msg = input.value.trim();
    if (!msg) return;

    appendChatMsg('User', msg);
    input.value = '';

    // Show temporary typing indicator
    const body = document.getElementById('gopcChatBody');
    const typingId = 'typing_' + Date.now();
    const typingDiv = document.createElement('div');
    typingDiv.id = typingId;
    typingDiv.className = 'gopc-msg bot';
    typingDiv.innerHTML = '<span style="color:#8c93a0;">🤖 正在思考与分析硬件数据...</span>';
    body.appendChild(typingDiv);
    body.scrollTop = body.scrollHeight;

    try {
      const formData = new FormData();
      formData.append('message', msg);
      formData.append('session_token', gopcSessionToken);

      const res = await fetch('index.php?page=chat-send-api', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();
      const el = document.getElementById(typingId);
      if (el) el.remove();

      if (data.reply) {
        appendChatMsg('AI', data.reply);
      } else {
        appendChatMsg('AI', '抱歉，暂时未能获取到响应，请稍后再试。');
      }
    } catch (err) {
      const el = document.getElementById(typingId);
      if (el) el.remove();
      appendChatMsg('AI', '网络通信异常，请确认服务器运行状态。');
    }
  }

  async function loadChatHistory() {
    try {
      const res = await fetch('index.php?page=chat-history-api&session_token=' + encodeURIComponent(gopcSessionToken));
      const logs = await res.json();
      if (Array.isArray(logs) && logs.length > 0) {
        const body = document.getElementById('gopcChatBody');
        body.innerHTML = '';
        logs.forEach(log => {
          const time = log.Timestamp ? log.Timestamp.substring(11, 16) : '';
          appendChatMsg(log.Sender, log.Message, time);
        });
      }
    } catch(e) {}
  }

  async function clearGopcChatHistory() {
    if (!confirm('确定清空当前对话记录吗？')) return;
    try {
      const formData = new FormData();
      formData.append('session_token', gopcSessionToken);
      await fetch('index.php?page=chat-clear-api', {
        method: 'POST',
        body: formData
      });
      document.getElementById('gopcChatBody').innerHTML = `
        <div class="gopc-msg bot">
          对话记录已清空。您可以随时提出任何装机或硬件疑问！
          <span class="gopc-msg-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
        </div>
      `;
    } catch(e) {}
  }
</script>
