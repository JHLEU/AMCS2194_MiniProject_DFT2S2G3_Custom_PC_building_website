<?php
/**
 * @var string $pageTitle
 * @var string $sessionToken
 * @var array $logs
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="public/css/style.css">
  <style>
    .container {
      max-width: 1240px;
      margin: 0 auto;
      padding: 30px 20px 60px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .chat-layout {
      display: grid;
      grid-template-columns: 320px 1fr;
      gap: 24px;
      height: 720px;
    }
    @media (max-width: 860px) {
      .chat-layout {
        grid-template-columns: 1fr;
        height: auto;
      }
    }

    /* Sidebar */
    .chat-sidebar {
      background: #12141a;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 14px;
      padding: 22px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .topic-chip {
      background: #161922;
      border: 1px solid rgba(255,255,255,0.08);
      color: #cbd5e1;
      padding: 10px 12px;
      border-radius: 8px;
      font-size: 12px;
      margin-bottom: 8px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: all 0.15s;
    }
    .topic-chip:hover {
      border-color: #ff334b;
      color: #fff;
      background: rgba(255,51,75,0.1);
    }

    /* Main Chat Box */
    .chat-main {
      background: #12141a;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 14px;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    .chat-header {
      background: #161922;
      padding: 16px 24px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .bot-profile {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .bot-avatar {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
    }

    .chat-messages {
      flex: 1;
      padding: 24px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 16px;
      background: #0d0f14;
    }
    .chat-msg-row {
      display: flex;
      gap: 12px;
      max-width: 82%;
      position: relative;
    }
    .chat-msg-row.user {
      align-self: flex-end;
      flex-direction: row-reverse;
    }
    .chat-msg-row.ai {
      align-self: flex-start;
    }

    .chat-bubble {
      padding: 14px 18px;
      border-radius: 14px;
      font-size: 13px;
      line-height: 1.6;
      word-break: break-word;
      position: relative;
    }
    .chat-msg-row.ai .chat-bubble {
      background: #161922;
      border: 1px solid rgba(255,255,255,0.08);
      color: #e2e8f0;
      border-top-left-radius: 4px;
    }
    .chat-msg-row.user .chat-bubble {
      background: #ff334b;
      color: #fff;
      border-top-right-radius: 4px;
      box-shadow: 0 4px 15px rgba(255,51,75,0.35);
    }
    .msg-meta {
      font-size: 10px;
      color: #8c93a0;
      margin-top: 6px;
      display: flex;
      justify-content: flex-end;
      gap: 8px;
    }
    .msg-del-btn {
      background: none;
      border: none;
      color: #5a6270;
      cursor: pointer;
      font-size: 11px;
    }
    .msg-del-btn:hover {
      color: #ff334b;
    }

    /* Input Footer */
    .chat-input-bar {
      background: #161922;
      padding: 16px 20px;
      border-top: 1px solid rgba(255,255,255,0.08);
      display: flex;
      gap: 12px;
      align-items: center;
    }
    .chat-textarea {
      flex: 1;
      background: #0d0f14;
      border: 1px solid rgba(255,255,255,0.15);
      border-radius: 10px;
      color: #fff;
      padding: 12px 16px;
      font-size: 14px;
      outline: none;
      font-family: inherit;
      resize: none;
      height: 48px;
    }
    .chat-textarea:focus {
      border-color: #ff334b;
    }
    .chat-send-btn {
      background: #ff334b;
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 0 24px;
      height: 48px;
      font-size: 14px;
      font-weight: 800;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(255,51,75,0.4);
      transition: background 0.15s;
    }
    .chat-send-btn:hover {
      background: #e0243a;
    }
  </style>
</head>
<body>

  <?php require __DIR__ . '/layout/navbar.php'; ?>

  <div class="container">

    <div style="margin-bottom: 20px;">
      <div style="font-size: 12px; color: #00f2fe; font-weight: 700; text-transform: uppercase;">Real-Time AI Hardware Assistant (CRUD)</div>
      <h1 style="font-size: 26px; font-weight: 800; color: #fff; margin-top: 4px;">💬 GOPC 24/7 AI 硬件装机顾问</h1>
      <p style="font-size: 13px; color: #8c93a0;">智能解答配件兼容、整机预算搭配与功耗瓶颈，支持直接查询真实数据库订单流水与发货单号！</p>
    </div>

    <div class="chat-layout">

      <!-- Left Sidebar: Session Info & Topic Shortcuts -->
      <div class="chat-sidebar">
        <div>
          <div style="font-size: 13px; font-weight: 700; color: #fff; margin-bottom: 12px;">⚡ 快速咨询话题 (Quick Prompts)</div>
          
          <div class="topic-chip" onclick="askPreset('⚡ 预算 RM 4000 适合玩主流电竞的配置方案')">
            <span>💡</span> <span>预算 RM 4000 电竞主机推荐</span>
          </div>
          <div class="topic-chip" onclick="askPreset('🔥 7800X3D 配 RTX 4080 Super 需要多大电源和散热？')">
            <span>🔥</span> <span>7800X3D + 4080S 旗舰搭配</span>
          </div>
          <div class="topic-chip" onclick="askPreset('🔍 帮我查询订单 ORD-20261101-8891 的装机进度')">
            <span>📦</span> <span>实时查询装机订单流水</span>
          </div>
          <div class="topic-chip" onclick="askPreset('💻 i7-14700K 搭配 4070Ti Super 会有性能瓶颈吗？')">
            <span>⚡</span> <span>i7 与 4070TiS 瓶颈测算</span>
          </div>
          <div class="topic-chip" onclick="askPreset('💳 网站支持哪些付款方式？提供分期吗？')">
            <span>💳</span> <span>付款方式与 3 年质保说明</span>
          </div>
          <div class="topic-chip" onclick="askPreset('📞 吉隆坡实体展厅地址和营业时间？')">
            <span>📍</span> <span>实体展厅与 WhatsApp 直连</span>
          </div>
        </div>

        <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 16px;">
          <div style="font-size: 11px; color: #8c93a0; margin-bottom: 10px;">
            会话标识：<code style="color: #00f2fe;"><?= htmlspecialchars(substr($sessionToken, 0, 16)) ?>...</code>
          </div>
          <button onclick="clearAllChat()" class="btn" style="width: 100%; justify-content: center; background: #161922; color: #ff334b; border: 1px solid rgba(255,51,75,0.3); padding: 9px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;">
            🗑️ 清空所有聊天记录 (Clear Chat)
          </button>
        </div>
      </div>

      <!-- Right Main: Chat Area -->
      <div class="chat-main">
        <div class="chat-header">
          <div class="bot-profile">
            <div class="bot-avatar">🤖</div>
            <div>
              <div style="font-size: 15px; font-weight: 800; color: #fff;">GOPC CyberAI 装机顾问</div>
              <div style="font-size: 12px; color: #10b981; display: flex; align-items: center; gap: 4px;">
                <span style="display:inline-block; width:6px; height:6px; background:#10b981; border-radius:50%;"></span>
                在线 | 毫秒级知识库匹配
              </div>
            </div>
          </div>
          <div style="display: flex; gap: 10px;">
            <a href="index.php?page=builder" class="btn" style="background: rgba(255,51,75,0.15); color: #ff334b; border: 1px solid rgba(255,51,75,0.3); padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; text-decoration: none;">
              💻 前往配件组装器
            </a>
          </div>
        </div>

        <!-- Chat Stream -->
        <div class="chat-messages" id="chatStream">
          <?php if (empty($logs)): ?>
            <div class="chat-msg-row ai" id="welcomeMsg">
              <div class="chat-bubble">
                您好！我是 GOPC 智能装机顾问 🤖<br><br>
                无论您是想咨询<strong>特定预算的性价比配置</strong>、测算<strong>CPU与显卡是否有瓶颈</strong>，还是发送<strong>订单编号 (如 `ORD-20261101-8891`)</strong> 查询实时装机流水与发货单号，我都能为您即刻解答！
                <div class="msg-meta">
                  <span><?= date('H:i') ?></span>
                </div>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($logs as $log): ?>
              <div class="chat-msg-row <?= $log['Sender'] === 'User' ? 'user' : 'ai' ?>" id="msgRow_<?= $log['ChatID'] ?>">
                <div class="chat-bubble">
                  <?= nl2br(preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', htmlspecialchars($log['Message']))) ?>
                  <div class="msg-meta">
                    <span><?= substr($log['Timestamp'], 11, 16) ?></span>
                    <button class="msg-del-btn" onclick="deleteMessage(<?= $log['ChatID'] ?>)" title="删除此条消息">✕</button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Input Bar -->
        <form class="chat-input-bar" onsubmit="handleSend(event)">
          <textarea id="chatInput" class="chat-textarea" placeholder="输入您的装机疑问或订单编号 (按 Enter 发送)..." onkeydown="checkEnter(event)"></textarea>
          <button type="submit" id="btnSend" class="chat-send-btn">发送</button>
        </form>
      </div>

    </div>

  </div>

  <?php require __DIR__ . '/layout/footer.php'; ?>

  <script>
    const sessionToken = '<?= htmlspecialchars($sessionToken) ?>';

    function checkEnter(e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        handleSend(new Event('submit'));
      }
    }

    function askPreset(prompt) {
      document.getElementById('chatInput').value = prompt;
      handleSend(new Event('submit'));
    }

    function appendMessage(sender, text, time, id) {
      const stream = document.getElementById('chatStream');
      const row = document.createElement('div');
      row.className = 'chat-msg-row ' + (sender === 'User' ? 'user' : 'ai');
      if (id) row.id = 'msgRow_' + id;

      const timeStr = time || new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
      let formatted = text
        .replace(/\n/g, '<br>')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/`([^`]+)`/g, '<code style="background:rgba(255,255,255,0.12);padding:2px 5px;border-radius:4px;color:#00f2fe;">$1</code>');

      const delBtn = id ? `<button class="msg-del-btn" onclick="deleteMessage(${id})">✕</button>` : '';

      row.innerHTML = `
        <div class="chat-bubble">
          ${formatted}
          <div class="msg-meta">
            <span>${timeStr}</span>
            ${delBtn}
          </div>
        </div>
      `;
      stream.appendChild(row);
      stream.scrollTop = stream.scrollHeight;
    }

    async function handleSend(e) {
      if (e) e.preventDefault();
      const input = document.getElementById('chatInput');
      const msg = input.value.trim();
      if (!msg) return;

      appendMessage('User', msg);
      input.value = '';

      // Typing indicator
      const stream = document.getElementById('chatStream');
      const typingId = 'typing_' + Date.now();
      const typingRow = document.createElement('div');
      typingRow.id = typingId;
      typingRow.className = 'chat-msg-row ai';
      typingRow.innerHTML = `
        <div class="chat-bubble" style="color: #8c93a0;">
          🤖 正在查阅硬件参数与数据库...
        </div>
      `;
      stream.appendChild(typingRow);
      stream.scrollTop = stream.scrollHeight;

      try {
        const formData = new FormData();
        formData.append('message', msg);
        formData.append('session_token', sessionToken);

        const res = await fetch('index.php?page=chat-send-api', {
          method: 'POST',
          body: formData
        });
        const d = await res.json();
        const el = document.getElementById(typingId);
        if (el) el.remove();

        if (d.reply) {
          appendMessage('AI', d.reply, d.timestamp, d.ai_msg_id);
        }
      } catch(err) {
        const el = document.getElementById(typingId);
        if (el) el.remove();
        appendMessage('AI', '网络故障，未能连接至 AI 服务。');
      }
    }

    async function deleteMessage(id) {
      if (!confirm('确定删除此条对话记录吗？')) return;
      try {
        const formData = new FormData();
        formData.append('chat_id', id);
        await fetch('index.php?page=chat-del-api', { method: 'POST', body: formData });
        const row = document.getElementById('msgRow_' + id);
        if (row) row.remove();
      } catch(e) {}
    }

    async function clearAllChat() {
      if (!confirm('确定清空所有对话记录吗？')) return;
      try {
        const formData = new FormData();
        formData.append('session_token', sessionToken);
        await fetch('index.php?page=chat-clear-api', { method: 'POST', body: formData });
        document.getElementById('chatStream').innerHTML = `
          <div class="chat-msg-row ai">
            <div class="chat-bubble">
              对话已清空。您可以随时提出任何装机或订单疑问！
              <div class="msg-meta">
                <span>${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
              </div>
            </div>
          </div>
        `;
      } catch(e) {}
    }

    // Scroll to bottom on load
    document.addEventListener('DOMContentLoaded', () => {
      const stream = document.getElementById('chatStream');
      stream.scrollTop = stream.scrollHeight;
    });
  </script>
</body>
</html>
