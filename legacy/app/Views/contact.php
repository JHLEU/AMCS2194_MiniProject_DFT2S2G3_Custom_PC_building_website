<?php
/**
 * @var string $pageTitle
 * @var string $hotline
 * @var string $whatsapp
 * @var string $email
 * @var string $address
 * @var string $hours
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
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px 80px;
      flex: 1;
    }

    /* Top Action Cards */
    .contact-channels {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      margin-bottom: 35px;
    }
    .channel-card {
      background: #12141a;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 14px;
      padding: 26px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: all 0.2s;
    }
    .channel-card:hover {
      border-color: rgba(255,51,75,0.4);
      transform: translateY(-2px);
    }
    .channel-icon {
      font-size: 32px;
      margin-bottom: 12px;
    }
    .channel-title {
      font-size: 18px;
      font-weight: 800;
      color: #fff;
      margin-bottom: 6px;
    }
    .channel-desc {
      font-size: 13px;
      color: #8c93a0;
      line-height: 1.5;
      margin-bottom: 18px;
    }
    .channel-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 12px 20px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 800;
      text-decoration: none;
      transition: all 0.15s;
    }

    /* Layout */
    .contact-grid {
      display: grid;
      grid-template-columns: 1fr 440px;
      gap: 30px;
      align-items: start;
    }
    @media (max-width: 900px) {
      .contact-grid {
        grid-template-columns: 1fr;
      }
    }

    .card-box {
      background: #12141a;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 12px;
      padding: 26px;
      margin-bottom: 24px;
    }
    .card-title {
      font-size: 16px;
      font-weight: 800;
      color: #fff;
      margin-bottom: 18px;
      border-bottom: 1px solid rgba(255,255,255,0.06);
      padding-bottom: 12px;
    }

    .form-group {
      margin-bottom: 16px;
    }
    .form-group label {
      display: block;
      font-size: 12px;
      font-weight: 700;
      color: #8c93a0;
      margin-bottom: 6px;
    }
    .form-input {
      width: 100%;
      background: #0d0f14;
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 8px;
      padding: 10px 14px;
      color: #fff;
      font-size: 13px;
      outline: none;
      font-family: inherit;
    }
    .form-input:focus {
      border-color: #ff334b;
    }

    /* WhatsApp Chips */
    .wa-chip {
      background: #161922;
      border: 1px solid rgba(255,255,255,0.1);
      color: #cbd5e1;
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 12px;
      cursor: pointer;
      display: block;
      margin-bottom: 8px;
      text-decoration: none;
      transition: all 0.15s;
    }
    .wa-chip:hover {
      background: rgba(37, 211, 102, 0.15);
      border-color: #25d366;
      color: #25d366;
    }
  </style>
</head>
<body>

  <?php require __DIR__ . '/layout/navbar.php'; ?>

  <div class="container">

    <div style="margin-bottom: 28px;">
      <div style="font-size: 12px; color: #25d366; font-weight: 700; text-transform: uppercase;">Direct Assistance & Showroom</div>
      <h1 style="font-size: 28px; font-weight: 800; color: #fff; margin-top: 4px;">📞 联系我们 (Call / WhatsApp) & 实体展厅</h1>
      <p style="font-size: 13px; color: #8c93a0;">无论是装机方案建议、物流进度核验，还是三年保固技术排期，专业工程师随时为您待命。</p>
    </div>

    <!-- 3 Big Action Channels -->
    <div class="contact-channels">

      <!-- WhatsApp Channel -->
      <div class="channel-card" style="border-color: rgba(37, 211, 102, 0.3);">
        <div>
          <div class="channel-icon">💬</div>
          <div class="channel-title">WhatsApp 官方即时客服</div>
          <p class="channel-desc">平均 5 分钟内极速响应！支持直接发送配置单截图、视频通话看装机走线进度。</p>
        </div>
        <a href="https://wa.me/60121112222?text=Hi%20JUSPC,%20I%20would%20like%20to%20consult%20about%20a%20Custom%20PC" target="_blank" class="channel-btn" style="background: #25d366; color: #000;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.288.043.088.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.203c.044.072.044.419-.1.824z"/></svg>
          点击开启 WhatsApp 对话
        </a>
      </div>

      <!-- Direct Phone Call Channel -->
      <div class="channel-card" style="border-color: rgba(0, 242, 254, 0.3);">
        <div>
          <div class="channel-icon">📞</div>
          <div class="channel-title">电话热线 (Direct Hotline)</div>
          <p class="channel-desc">装机顾问直通热线：<?= htmlspecialchars($hotline) ?>。周一至周日全天 10:00 - 21:00 守候解答。</p>
        </div>
        <a href="tel:+60121112222" class="channel-btn" style="background: #00f2fe; color: #000;">
          📞 立即拨打咨询电话
        </a>
      </div>

      <!-- Live AI Chat Channel -->
      <div class="channel-card" style="border-color: rgba(255, 51, 75, 0.3);">
        <div>
          <div class="channel-icon">🤖</div>
          <div class="channel-title">24/7 AI 硬件顾问</div>
          <p class="channel-desc">毫秒级回答显卡算力、电源功耗、预算搭配以及根据订单号查询实时装机流水！</p>
        </div>
        <a href="index.php?page=chat" class="channel-btn" style="background: #ff334b; color: #fff;">
          💬 进入 AI 对话专区
        </a>
      </div>

    </div>

    <!-- Main Content: Form + Showroom Info -->
    <div class="contact-grid">

      <!-- Left Column: Inquiry Ticket Form -->
      <div class="card-box">
        <div class="card-title">
          <span>📝 提报装机咨询或售后技术工单 (Inquiry Ticket)</span>
        </div>

        <form id="contactForm" onsubmit="submitContactTicket(event)">
          <div class="form-group">
            <label>咨询主题 (Inquiry Subject)</label>
            <select id="ticketSubject" class="form-input">
              <option value="全新定制电竞电脑整机报价">全新定制电竞电脑整机报价</option>
              <option value="现有订单物流与装机进度追踪">现有订单物流与装机进度追踪</option>
              <option value="3年保固维修与硬件升级建议">3年保固维修与硬件升级建议</option>
              <option value="企业办公室与工作室批量采购">企业办公室与工作室批量采购</option>
            </select>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div class="form-group">
              <label>您的称呼 (Full Name) *</label>
              <input type="text" id="ticketName" class="form-input" placeholder="例如：Kelvin Wong" required>
            </div>
            <div class="form-group">
              <label>联系电话 / WhatsApp *</label>
              <input type="text" id="ticketPhone" class="form-input" placeholder="+60 12-345 6789" required>
            </div>
          </div>

          <div class="form-group">
            <label>电子邮箱 (Email)</label>
            <input type="email" id="ticketEmail" class="form-input" placeholder="kelvin@gmail.com">
          </div>

          <div class="form-group">
            <label>详细需求或问题说明 *</label>
            <textarea id="ticketMsg" class="form-input" rows="4" placeholder="请详细描述您的预算、主要玩的游戏或遇到的硬件故障情况..." required></textarea>
          </div>

          <button type="submit" id="btnSubmitTicket" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 13px; font-weight: 800; background: #ff334b; color: #fff; border: none; border-radius: 8px; cursor: pointer; box-shadow: 0 4px 15px rgba(255,51,75,0.4);">
            📩 立即提交咨询工单
          </button>
        </form>

        <div id="ticketSuccessMsg" style="display: none; background: rgba(16,185,129,0.1); border: 1px solid #10b981; color: #10b981; padding: 14px; border-radius: 8px; margin-top: 16px; font-size: 13px;">
          ✓ 工单提报成功！客服专员将在 15 分钟内通过 WhatsApp 或电话联系您。
        </div>
      </div>

      <!-- Right Column: Showroom & WhatsApp Pre-filled Quick Links -->
      <div>

        <!-- Quick WhatsApp Topics -->
        <div class="card-box">
          <div class="card-title">
            <span>⚡ WhatsApp 快捷预填交谈 (1-Click Chats)</span>
          </div>
          <p style="font-size: 12px; color: #8c93a0; margin-bottom: 12px;">点击即可直接携带预设主题进入 WhatsApp 对话：</p>
          
          <a href="https://wa.me/60121112222?text=Hi%20JUSPC,%20I%20have%20a%20budget%20of%20RM%204000%20-%205000,%20please%20recommend%20a%20gaming%20PC%20build" target="_blank" class="wa-chip">
            💬 <strong>预算 RM 4000-5000 咨询：</strong> "推荐主流 2K 电竞主机方案"
          </a>
          <a href="https://wa.me/60121112222?text=Hi%20JUSPC,%20I%20want%20to%20build%20a%20pure%20white%20aesthetic%20PC" target="_blank" class="wa-chip">
            💬 <strong>高颜值海景房咨询：</strong> "想组装全白吹雪 RGB 主机"
          </a>
          <a href="https://wa.me/60121112222?text=Hi%20JUSPC,%20I%20want%20to%20check%20my%20order%20status" target="_blank" class="wa-chip">
            💬 <strong>订单查询追踪：</strong> "核验我的订单流水与出库发货单号"
          </a>
          <a href="https://wa.me/60121112222?text=Hi%20JUSPC,%20I%20need%20warranty%20and%20technical%20support" target="_blank" class="wa-chip">
            💬 <strong>技术售后与上门保固：</strong> "申请技术工程师一对一排障"
          </a>
        </div>

        <!-- Physical Showroom Location -->
        <div class="card-box">
          <div class="card-title">
            <span>📍 吉隆坡实体旗舰展厅</span>
          </div>
          <div style="font-size: 13px; color: #cbd5e1; line-height: 1.8;">
            <div>🏢 <strong>展厅地址：</strong> <?= htmlspecialchars($address) ?></div>
            <div>🕒 <strong>营业时间：</strong> <?= htmlspecialchars($hours) ?></div>
            <div>🚇 <strong>捷运交通：</strong> MRT Bukit Bintang 站 D 出口步行 3 分钟即达</div>
            <div>🅿️ <strong>停车指南：</strong> Plaza Low Yat 地下停车场 / Sungai Wang 停车场</div>
          </div>
          <div style="margin-top: 16px;">
            <a href="https://maps.google.com/?q=Plaza+Low+Yat+Kuala+Lumpur" target="_blank" class="btn" style="display: block; text-align: center; background: #161922; color: #00f2fe; border: 1px solid rgba(0,242,254,0.3); padding: 9px; border-radius: 8px; font-weight: 700; font-size: 13px; text-decoration: none;">
              🗺️ 在 Google Maps 中打开实体展厅位置 &nearr;
            </a>
          </div>
        </div>

      </div>

    </div>

  </div>

  <?php require __DIR__ . '/layout/footer.php'; ?>
  <?php require __DIR__ . '/layout/ai_chat_widget.php'; ?>

  <script>
    async function submitContactTicket(e) {
      e.preventDefault();
      const btn = document.getElementById('btnSubmitTicket');
      btn.disabled = true;
      btn.textContent = '正在提交工单...';

      const formData = new FormData();
      formData.append('subject', document.getElementById('ticketSubject').value);
      formData.append('name', document.getElementById('ticketName').value.trim());
      formData.append('phone', document.getElementById('ticketPhone').value.trim());
      formData.append('email', document.getElementById('ticketEmail').value.trim());
      formData.append('message', document.getElementById('ticketMsg').value.trim());

      try {
        const res = await fetch('index.php?page=api-submit-ticket', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();
        document.getElementById('ticketSuccessMsg').textContent = data.message || '✓ 工单提报成功！';
        document.getElementById('ticketSuccessMsg').style.display = 'block';
        document.getElementById('contactForm').reset();
      } catch(err) {
        alert('提交异常，请直接通过 WhatsApp 联系客服！');
      } finally {
        btn.disabled = false;
        btn.textContent = '📩 立即提交咨询工单';
      }
    }
  </script>
</body>
</html>
