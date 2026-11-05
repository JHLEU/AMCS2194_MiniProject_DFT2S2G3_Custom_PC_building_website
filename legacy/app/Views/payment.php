<?php
/**
 * @var string $pageTitle
 * @var string $buildTitle
 * @var array $items
 * @var float $total
 * @var string $customerName
 * @var string $customerPhone
 * @var string $customerEmail
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
    .checkout-layout {
      display: grid;
      grid-template-columns: 1fr 420px;
      gap: 30px;
      align-items: start;
    }
    @media (max-width: 900px) {
      .checkout-layout {
        grid-template-columns: 1fr;
      }
    }

    .card-box {
      background: #12141a;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 12px;
      padding: 24px;
      margin-bottom: 24px;
    }
    .card-title {
      font-size: 16px;
      font-weight: 800;
      color: #fff;
      margin-bottom: 18px;
      display: flex;
      align-items: center;
      gap: 8px;
      border-bottom: 1px solid rgba(255,255,255,0.06);
      padding-bottom: 12px;
    }

    /* Mode Toggle Switcher */
    .mode-switcher {
      display: flex;
      background: #0d0f14;
      padding: 4px;
      border-radius: 10px;
      border: 1px solid rgba(255,255,255,0.1);
      margin-bottom: 20px;
    }
    .mode-tab {
      flex: 1;
      text-align: center;
      padding: 8px 12px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 700;
      color: #8c93a0;
      cursor: pointer;
      border: none;
      background: none;
      transition: all 0.15s;
    }
    .mode-tab.active {
      background: #161922;
      color: #00f2fe;
      box-shadow: 0 2px 8px rgba(0,0,0,0.4);
      border: 1px solid rgba(0,242,254,0.3);
    }

    /* Payment Methods Grid */
    .pay-methods {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
      gap: 12px;
      margin-bottom: 20px;
    }
    .method-card {
      background: #0d0f14;
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 8px;
      padding: 14px 10px;
      text-align: center;
      cursor: pointer;
      transition: all 0.15s;
    }
    .method-card:hover {
      border-color: rgba(255,51,75,0.5);
    }
    .method-card.selected {
      background: rgba(255,51,75,0.1);
      border-color: #ff334b;
      box-shadow: 0 0 12px rgba(255,51,75,0.25);
    }
    .method-icon {
      font-size: 22px;
      margin-bottom: 6px;
    }
    .method-name {
      font-size: 12px;
      font-weight: 700;
      color: #fff;
    }

    /* Forms */
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }
    .form-group {
      margin-bottom: 14px;
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

    /* Bank Pills */
    .bank-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
      gap: 8px;
      margin-top: 10px;
    }
    .bank-item {
      background: #161922;
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 6px;
      padding: 8px;
      text-align: center;
      font-size: 11px;
      font-weight: 700;
      color: #e2e8f0;
      cursor: pointer;
    }
    .bank-item.selected {
      border-color: #fbbf24;
      background: rgba(251,191,36,0.15);
      color: #fbbf24;
    }

    /* QR Frame */
    .qr-frame {
      background: #fff;
      border-radius: 12px;
      padding: 16px;
      display: inline-block;
      text-align: center;
      box-shadow: 0 4px 20px rgba(0,0,0,0.5);
    }

    /* Summary list */
    .summary-item-row {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      padding: 8px 0;
      border-bottom: 1px dashed rgba(255,255,255,0.06);
    }

    /* Success Receipt Modal */
    .receipt-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.85);
      backdrop-filter: blur(6px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      padding: 20px;
    }
    .receipt-box {
      background: #12141a;
      border: 1px solid rgba(16,185,129,0.4);
      box-shadow: 0 20px 50px rgba(0,0,0,0.8), 0 0 25px rgba(16,185,129,0.25);
      border-radius: 16px;
      width: 100%;
      max-width: 520px;
      padding: 30px;
      text-align: center;
    }
  </style>
</head>
<body>

  <?php require __DIR__ . '/layout/navbar.php'; ?>

  <div class="container">

    <div style="margin-bottom: 24px;">
      <div style="font-size: 12px; color: #ff334b; font-weight: 700; text-transform: uppercase;">Secure Payment Gateway (Checkout)</div>
      <h1 style="font-size: 28px; font-weight: 800; color: #fff; margin-top: 4px;">在线安全收银台 & 订单支付</h1>
      <p style="font-size: 13px; color: #8c93a0;">支持全马主流网银 FPX、信用卡/借记卡、Touch 'n Go eWallet 及 DuitNow QR 即时核验结账。</p>
    </div>

    <!-- Mode Switcher -->
    <div class="mode-switcher">
      <button class="mode-tab active" id="tabDemo" onclick="switchPayMode('demo')">
        🟢 演示仿真沙盒模式 (Demo Sandbox Mode)
      </button>
      <button class="mode-tab" id="tabReal" onclick="switchPayMode('real')">
        🔵 实时在线 API 模式 (Real API Gateway Mode)
      </button>
    </div>

    <div class="checkout-layout">

      <!-- Left Column: Customer details & payment method -->
      <div>

        <!-- Real API Config Box (conditionally displayed) -->
        <div class="card-box" id="realApiConfigBox" style="display: none; border-color: rgba(0,242,254,0.3); background: rgba(0,242,254,0.03);">
          <div class="card-title" style="color: #00f2fe;">
            <span>🔌 Real API Gateway 开发者参数设置</span>
          </div>
          <div class="form-grid">
            <div class="form-group">
              <label>网关提供商 (Gateway Provider)</label>
              <select id="apiProvider" class="form-input">
                <option value="toyyibpay">ToyyibPay (Malaysia FPX Gateway)</option>
                <option value="stripe">Stripe Payments (Visa/Mastercard)</option>
                <option value="billplz">Billplz Direct Pay</option>
              </select>
            </div>
            <div class="form-group">
              <label>API Secret Key / User Secret</label>
              <input type="password" id="apiSecretKey" class="form-input" value="sec_test_88f921ab0912c" placeholder="sk_live_...">
            </div>
          </div>
          <div style="font-size: 11px; color: #8c93a0;">系统已预配测试环境 Secret Key，提交时将与网关进行实时握手。</div>
        </div>

        <!-- Section 1: Customer Contact & Shipping Details -->
        <div class="card-box">
          <div class="card-title">
            <span>📦 1. 收件人与配送信息</span>
          </div>
          <div class="form-grid">
            <div class="form-group">
              <label>客户姓名 (Full Name) *</label>
              <input type="text" id="custName" class="form-input" value="<?= htmlspecialchars($customerName) ?>" required>
            </div>
            <div class="form-group">
              <label>联系电话 / WhatsApp *</label>
              <input type="text" id="custPhone" class="form-input" value="<?= htmlspecialchars($customerPhone) ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label>电子邮箱 (Email for Invoice Receipt) *</label>
            <input type="email" id="custEmail" class="form-input" value="<?= htmlspecialchars($customerEmail) ?>" required>
          </div>
          <div class="form-group">
            <label>收件详细地址 (Shipping Address) *</label>
            <textarea id="custAddress" class="form-input" rows="2" placeholder="请输入送货地址，或注明：吉隆坡 Plaza Low Yat 展厅自提">No 18, Jalan Pavilion 2, Bukit Bintang, 55100 Kuala Lumpur</textarea>
          </div>
        </div>

        <!-- Section 2: Payment Method Selection -->
        <div class="card-box">
          <div class="card-title">
            <span>💳 2. 选择安全支付方式</span>
          </div>

          <div class="pay-methods">
            <div class="method-card selected" onclick="selectMethod('fpx', this)">
              <div class="method-icon">🏦</div>
              <div class="method-name">FPX 网银</div>
              <div style="font-size: 10px; color: #8c93a0; margin-top: 2px;">Maybank/CIMB</div>
            </div>
            <div class="method-card" onclick="selectMethod('tng', this)">
              <div class="method-icon">📱</div>
              <div class="method-name">Touch 'n Go</div>
              <div style="font-size: 10px; color: #8c93a0; margin-top: 2px;">eWallet 扫码</div>
            </div>
            <div class="method-card" onclick="selectMethod('card', this)">
              <div class="method-icon">💳</div>
              <div class="method-name">信用卡/借记卡</div>
              <div style="font-size: 10px; color: #8c93a0; margin-top: 2px;">Visa/Master</div>
            </div>
            <div class="method-card" onclick="selectMethod('duitnow', this)">
              <div class="method-icon">🪙</div>
              <div class="method-name">DuitNow QR</div>
              <div style="font-size: 10px; color: #8c93a0; margin-top: 2px;">跨行即时转账</div>
            </div>
          </div>

          <!-- FPX Bank Selection Panel -->
          <div id="panelFpx">
            <div style="font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 8px;">选择您的扣款银行：</div>
            <div class="bank-grid">
              <div class="bank-item selected" onclick="selectBank('Maybank2u', this)">Maybank2u</div>
              <div class="bank-item" onclick="selectBank('CIMB Clicks', this)">CIMB Clicks</div>
              <div class="bank-item" onclick="selectBank('Public Bank', this)">Public Bank</div>
              <div class="bank-item" onclick="selectBank('RHB Now', this)">RHB Now</div>
              <div class="bank-item" onclick="selectBank('Hong Leong', this)">Hong Leong</div>
              <div class="bank-item" onclick="selectBank('AmBank', this)">AmBank</div>
            </div>
          </div>

          <!-- Card Input Panel -->
          <div id="panelCard" style="display: none; margin-top: 15px;">
            <div class="form-group">
              <label>银行卡号 (Card Number)</label>
              <input type="text" class="form-input" placeholder="4111 2222 3333 4444" value="4111 2222 3333 4444">
            </div>
            <div class="form-grid">
              <div class="form-group">
                <label>有效期 (MM/YY)</label>
                <input type="text" class="form-input" placeholder="12/28" value="12/28">
              </div>
              <div class="form-group">
                <label>安全码 (CVV)</label>
                <input type="text" class="form-input" placeholder="888" value="888">
              </div>
            </div>
          </div>

          <!-- QR Code Panel (TNG / DuitNow) -->
          <div id="panelQr" style="display: none; text-align: center; padding: 15px 0;">
            <div class="qr-frame">
              <!-- SVG QR Code Mock -->
              <svg width="150" height="150" viewBox="0 0 100 100" fill="#000">
                <rect width="100" height="100" fill="#fff"/>
                <rect x="10" y="10" width="25" height="25" fill="#000"/>
                <rect x="15" y="15" width="15" height="15" fill="#fff"/>
                <rect x="18" y="18" width="9" height="9" fill="#000"/>
                
                <rect x="65" y="10" width="25" height="25" fill="#000"/>
                <rect x="70" y="15" width="15" height="15" fill="#fff"/>
                <rect x="73" y="18" width="9" height="9" fill="#000"/>

                <rect x="10" y="65" width="25" height="25" fill="#000"/>
                <rect x="15" y="70" width="15" height="15" fill="#fff"/>
                <rect x="18" y="73" width="9" height="9" fill="#000"/>

                <circle cx="50" cy="50" r="10" fill="#ff334b"/>
                <rect x="42" y="15" width="6" height="20" fill="#000"/>
                <rect x="42" y="65" width="16" height="6" fill="#000"/>
                <rect x="65" y="45" width="20" height="6" fill="#000"/>
              </svg>
            </div>
            <div style="font-size: 13px; font-weight: 700; color: #fff; margin-top: 10px;">请使用电子钱包 App 扫描上方二维码</div>
            <div style="font-size: 11px; color: #8c93a0;">二维码有效时间剩余：<span style="color:#fbbf24;">14分58秒</span></div>
          </div>

        </div>

      </div>

      <!-- Right Column: Order Summary & Checkout Button -->
      <div>
        <div class="card-box" style="position: sticky; top: 90px;">
          <div class="card-title">
            <span>🛒 结算清单与金额</span>
          </div>

          <div style="font-weight: 700; font-size: 14px; color: #fff; margin-bottom: 12px;">
            <?= htmlspecialchars($buildTitle) ?>
          </div>

          <!-- Items list -->
          <div style="max-height: 240px; overflow-y: auto; margin-bottom: 16px;">
            <?php foreach ($items as $item): ?>
              <div class="summary-item-row">
                <span style="color: #8c93a0; max-width: 230px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  <?= htmlspecialchars($item['name']) ?>
                </span>
                <span style="color: #fff; font-weight: 700;">
                  RM <?= number_format($item['price'], 2) ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>

          <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 12px; margin-bottom: 16px; font-size: 13px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; color: #8c93a0;">
              <span>硬件小计 (Subtotal)：</span>
              <span style="color: #fff;">RM <?= number_format($total, 2) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; color: #8c93a0;">
              <span>顺丰/J&T 顺心保价顺丰特惠：</span>
              <span style="color: #10b981; font-weight: 700;">免运费 (FREE)</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; color: #8c93a0;">
              <span>专业走线与烤机检测服务：</span>
              <span style="color: #10b981; font-weight: 700;">免费赠送</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding-top: 10px; border-top: 1px dashed rgba(255,255,255,0.1); align-items: baseline;">
              <span style="font-size: 14px; font-weight: 700; color: #fff;">应付总额 (MYR)：</span>
              <span style="font-size: 26px; font-weight: 800; color: #00f2fe;" id="displayGrandTotal">
                RM <?= number_format($total, 2) ?>
              </span>
            </div>
          </div>

          <button class="btn btn-primary" id="btnPayNow" style="width: 100%; justify-content: center; padding: 14px; font-size: 15px; background: #ff334b; color: #fff; border: none; border-radius: 8px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 20px rgba(255,51,75,0.45);" onclick="executePayment()">
            🔒 立即确认并完成支付 (Pay Now)
          </button>

          <div style="text-align: center; margin-top: 12px; font-size: 11px; color: #5a6270;">
            🔒 256-Bit SSL 加密交易 &bull; 真实入库流水号 &bull; 原厂 3 年质保
          </div>
        </div>
      </div>

    </div>

  </div>

  <!-- Success Receipt Modal -->
  <div class="receipt-modal-overlay" id="receiptModal">
    <div class="receipt-box">
      <div style="font-size: 50px; margin-bottom: 12px;">🎉</div>
      <h2 style="font-size: 22px; font-weight: 800; color: #10b981; margin-bottom: 6px;">支付已成功确认！</h2>
      <p style="font-size: 13px; color: #8c93a0; margin-bottom: 20px;">您的专属 PC 组装订单已正式下达，硬件仓管正在分配并核验序列号。</p>

      <div style="background: #0d0f14; border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 18px; text-align: left; margin-bottom: 24px; font-size: 13px; line-height: 1.8;">
        <div style="display: flex; justify-content: space-between;">
          <span style="color: #8c93a0;">订单编号 (Order No)：</span>
          <strong style="color: #00f2fe;" id="receiptOrderNo">ORD-20261106-XXXX</strong>
        </div>
        <div style="display: flex; justify-content: space-between;">
          <span style="color: #8c93a0;">交易流水 (Txn ID)：</span>
          <span style="color: #fff;" id="receiptTxnId">TXN-20261106-XXXX</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
          <span style="color: #8c93a0;">物流运单 (Tracking No)：</span>
          <span style="color: #fbbf24;" id="receiptTrackNo">JT-MY889920192MY</span>
        </div>
        <div style="display: flex; justify-content: space-between; border-top: 1px dashed rgba(255,255,255,0.08); margin-top: 8px; padding-top: 8px;">
          <span style="color: #8c93a0;">实付金额：</span>
          <strong style="color: #00f2fe; font-size: 16px;" id="receiptAmount">RM 0.00</strong>
        </div>
      </div>

      <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
        <button class="btn" style="background: #161922; color: #fff; border: 1px solid rgba(255,255,255,0.15); padding: 10px 18px; border-radius: 8px; font-weight: 700;" onclick="window.print()">
          🖨️ 打印发票 Receipt
        </button>
        <button class="btn" id="btnGoTrack" style="background: #00f2fe; color: #000; border: none; padding: 10px 22px; border-radius: 8px; font-weight: 800; cursor: pointer;">
          📦 实时追踪此订单进度 &rarr;
        </button>
      </div>
    </div>
  </div>

  <?php require __DIR__ . '/layout/footer.php'; ?>
  <?php require __DIR__ . '/layout/ai_chat_widget.php'; ?>

  <script>
    let currentMode = 'demo';
    let currentMethod = 'fpx';
    let selectedBank = 'Maybank2u';
    const checkoutItems = <?= json_encode($items) ?>;
    const checkoutTotal = <?= (float)$total ?>;

    function switchPayMode(mode) {
      currentMode = mode;
      document.getElementById('tabDemo').classList.toggle('active', mode === 'demo');
      document.getElementById('tabReal').classList.toggle('active', mode === 'real');
      document.getElementById('realApiConfigBox').style.display = (mode === 'real') ? 'block' : 'none';
    }

    function selectMethod(method, el) {
      currentMethod = method;
      document.querySelectorAll('.method-card').forEach(c => c.classList.remove('selected'));
      el.classList.add('selected');

      document.getElementById('panelFpx').style.display = (method === 'fpx') ? 'block' : 'none';
      document.getElementById('panelCard').style.display = (method === 'card') ? 'block' : 'none';
      document.getElementById('panelQr').style.display = (method === 'tng' || method === 'duitnow') ? 'block' : 'none';
    }

    function selectBank(name, el) {
      selectedBank = name;
      document.querySelectorAll('.bank-item').forEach(b => b.classList.remove('selected'));
      el.classList.add('selected');
    }

    async function executePayment() {
      const name = document.getElementById('custName').value.trim();
      const phone = document.getElementById('custPhone').value.trim();
      const email = document.getElementById('custEmail').value.trim();
      const addr = document.getElementById('custAddress').value.trim();

      if (!name || !phone || !email) {
        alert('请完整填写收件人姓名、电话和电子邮箱！');
        return;
      }

      const btn = document.getElementById('btnPayNow');
      btn.disabled = true;
      btn.textContent = (currentMode === 'real') ? '正在连接支付网关 API...' : '正在进行沙盒即时转账核验...';

      // Format payment method text
      let payDesc = currentMethod.toUpperCase();
      if (currentMethod === 'fpx') payDesc = 'FPX (' + selectedBank + ')';
      if (currentMethod === 'tng') payDesc = 'Touch n Go eWallet';
      if (currentMethod === 'card') payDesc = 'Credit/Debit Card';
      if (currentMethod === 'duitnow') payDesc = 'DuitNow QR';

      const formData = new FormData();
      formData.append('is_real_api', currentMode === 'real');
      formData.append('api_key', document.getElementById('apiSecretKey').value);
      formData.append('payment_method', payDesc);
      formData.append('customer_name', name);
      formData.append('customer_phone', phone);
      formData.append('customer_email', email);
      formData.append('shipping_address', addr);
      formData.append('total_amount', checkoutTotal);
      formData.append('items', JSON.stringify(checkoutItems));

      try {
        const res = await fetch('index.php?page=api-process-payment', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        if (data.success) {
          document.getElementById('receiptOrderNo').textContent = data.order_number;
          document.getElementById('receiptTxnId').textContent = data.transaction_id;
          document.getElementById('receiptTrackNo').textContent = data.tracking_number;
          document.getElementById('receiptAmount').textContent = 'RM ' + parseFloat(data.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2});

          document.getElementById('btnGoTrack').onclick = function() {
            window.location.href = 'index.php?page=tracking&order_no=' + encodeURIComponent(data.order_number);
          };

          document.getElementById('receiptModal').style.display = 'flex';
        } else {
          alert(data.message || '支付处理失败');
        }
      } catch (err) {
        alert('通信错误，请检查服务器！');
      } finally {
        btn.disabled = false;
        btn.textContent = '🔒 立即确认并完成支付 (Pay Now)';
      }
    }
  </script>
</body>
</html>
