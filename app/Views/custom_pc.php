<?php
/**
 * @var string $pageTitle
 * @var array $components
 * @var array $presets
 * @var array|null $loadedBuild
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'JUSPC Custom PC Configurator') ?></title>
  <link rel="stylesheet" href="public/css/style.css">
  <style>
    .container {
      max-width: 1280px;
      margin: 0 auto;
      padding: 30px 20px 60px;
      width: 100%;
      flex: 1;
    }

    /* Preset Bar */
    .preset-bar {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 16px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      flex-wrap: wrap;
      gap: 14px;
    }
    .preset-title {
      font-size: 14px;
      font-weight: 700;
      color: var(--text-muted);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .preset-pills {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .preset-btn {
      background: var(--bg-surface);
      border: 1px solid var(--border-color);
      color: var(--text-main);
      padding: 7px 16px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.15s;
    }
    .preset-btn:hover {
      border-color: var(--primary);
      color: #fff;
      transform: translateY(-1px);
    }

    /* Grid Layout */
    .builder-layout {
      display: grid;
      grid-template-columns: 1fr 390px;
      gap: 28px;
      align-items: start;
    }

    @media (max-width: 992px) {
      .builder-layout {
        grid-template-columns: 1fr;
      }
    }

    /* Category Cards */
    .category-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      margin-bottom: 20px;
      overflow: hidden;
      transition: border-color 0.2s;
    }
    .category-card:hover {
      border-color: rgba(255, 255, 255, 0.15);
    }
    .category-header {
      background: var(--bg-surface);
      padding: 14px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid var(--border-color);
    }
    .category-title {
      font-size: 15px;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .items-list {
      padding: 16px 20px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .item-option {
      background: #0d0f14;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 14px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .item-option:hover {
      background: #141822;
      border-color: rgba(0, 242, 254, 0.3);
    }
    .item-option.selected {
      background: rgba(255, 51, 75, 0.08);
      border-color: var(--primary);
      box-shadow: 0 0 10px rgba(255, 51, 75, 0.2);
    }

    .item-info {
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .item-radio {
      accent-color: var(--primary);
      width: 18px;
      height: 18px;
      cursor: pointer;
    }
    .item-details h4 {
      font-size: 14px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 3px;
    }
    .item-details p {
      font-size: 12px;
      color: var(--text-muted);
    }
    .item-price {
      font-size: 16px;
      font-weight: 800;
      color: var(--accent-cyan);
      white-space: nowrap;
    }

    /* Summary Sidebar */
    .summary-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 24px;
      position: sticky;
      top: 90px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
    }
    .summary-title {
      font-size: 18px;
      font-weight: 800;
      margin-bottom: 18px;
      padding-bottom: 12px;
      border-bottom: 1px solid var(--border-color);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .summary-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-bottom: 20px;
      max-height: 280px;
      overflow-y: auto;
    }
    .summary-item {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      gap: 8px;
      border-bottom: 1px dashed rgba(255,255,255,0.06);
      padding-bottom: 6px;
    }
    .summary-item-label {
      color: var(--text-muted);
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .summary-item-price {
      font-weight: 700;
      color: #fff;
      white-space: nowrap;
    }

    .power-box {
      background: var(--bg-surface);
      border-radius: 8px;
      padding: 12px 16px;
      margin-bottom: 20px;
    }
    .power-header {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      color: var(--text-muted);
      margin-bottom: 6px;
      font-weight: 600;
    }
    .power-val {
      color: #fbbf24;
      font-weight: 700;
    }
    .power-bar {
      height: 6px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 3px;
      overflow: hidden;
    }
    .power-fill {
      height: 100%;
      background: linear-gradient(90deg, #10b981 0%, #fbbf24 80%, #ff334b 100%);
      width: 55%;
      transition: width 0.3s ease;
    }

    .total-section {
      border-top: 1px solid var(--border-color);
      padding-top: 16px;
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: baseline;
    }
    .total-label {
      font-size: 14px;
      color: var(--text-muted);
      font-weight: 600;
    }
    .total-amount {
      font-size: 26px;
      font-weight: 800;
      color: var(--accent-cyan);
      letter-spacing: -0.02em;
    }

    /* Modal */
    .gopc-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      padding: 20px;
    }
    .gopc-modal {
      background: #12141a;
      border: 1px solid rgba(255,255,255,0.15);
      box-shadow: 0 15px 40px rgba(0,0,0,0.8), 0 0 20px rgba(255,51,75,0.25);
      border-radius: 14px;
      width: 100%;
      max-width: 500px;
      padding: 26px;
      animation: modalFadeIn 0.2s ease;
    }
    @keyframes modalFadeIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
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
      text-transform: uppercase;
    }
    .form-control {
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
    .form-control:focus {
      border-color: #ff334b;
    }

    /* Toast */
    #toast {
      position: fixed;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      background: #161922;
      border: 1px solid var(--primary);
      box-shadow: 0 10px 30px rgba(0,0,0,0.6), 0 0 15px rgba(255, 51, 75, 0.4);
      color: #fff;
      padding: 14px 24px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      display: none;
      z-index: 10001;
    }
  </style>
</head>
<body>

  <?php require __DIR__ . '/layout/navbar.php'; ?>

  <div class="container">

    <!-- Header bar with Loaded Build indicator if any -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
      <div>
        <h1 style="font-size: 26px; font-weight: 800; color: #fff;">💻 专属电竞主机配置器 (Custom Configurator)</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">自由挑选核心部件，系统实时核算兼容性、整机功耗 TDP 与官方总价。</p>
      </div>
      <div style="display: flex; gap: 10px; align-items: center;">
        <a href="index.php?page=saved-builds" class="btn" style="background: #161922; color: #00f2fe; border: 1px solid rgba(0,242,254,0.3); padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none;">
          📋 我的保存方案库 (CRUD)
        </a>
        <button class="btn" style="background: #161922; color: #8c93a0; border: 1px solid var(--border-color); padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;" onclick="resetConfig()">
          🔄 重置配置
        </button>
      </div>
    </div>

    <?php if ($loadedBuild): ?>
      <div style="background: rgba(0, 242, 254, 0.08); border: 1px solid #00f2fe; padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
          <span style="color: #00f2fe; font-weight: 800; font-size: 14px;">📂 正在编辑方案：<?= htmlspecialchars($loadedBuild['BuildName'] ?? '') ?></span>
          <span style="font-size: 12px; color: #8c93a0; margin-left: 10px;">(方案ID: #<?= $loadedBuild['BuildID'] ?> | 客户: <?= htmlspecialchars($loadedBuild['CustomerName'] ?? 'Guest') ?>)</span>
        </div>
        <a href="index.php?page=builder" style="font-size: 12px; color: #ff334b; text-decoration: none; font-weight: 700;">新建全新方案 &times;</a>
      </div>
    <?php endif; ?>

    <!-- Quick Presets -->
    <div class="preset-bar">
      <div class="preset-title">⚡ 快速预设方案推荐 (Presets):</div>
      <div class="preset-pills">
        <?php foreach ($presets as $preset): ?>
          <button class="preset-btn" onclick="applyPreset(<?= htmlspecialchars(json_encode($preset)) ?>)">
            <?= htmlspecialchars($preset['name']) ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Two-Column Builder Layout -->
    <div class="builder-layout">

      <!-- Left Column: Categories & Items List -->
      <div class="components-column">
        <?php foreach ($components as $catKey => $category): ?>
          <div class="category-card" data-category="<?= htmlspecialchars($catKey) ?>">
            <div class="category-header">
              <div class="category-title">
                <span><?= $category['icon'] ?? '⚙️' ?></span>
                <span><?= htmlspecialchars($category['title'] ?? ucfirst($catKey)) ?></span>
              </div>
              <span style="font-size: 12px; color: var(--text-muted);">已选配 1 项</span>
            </div>
            <div class="items-list">
              <?php foreach ($category['items'] as $item): ?>
                <label class="item-option <?= !empty($item['selected']) ? 'selected' : '' ?>" 
                       onclick="selectComponent('<?= htmlspecialchars($catKey) ?>', '<?= htmlspecialchars($item['id']) ?>', '<?= htmlspecialchars(addslashes($item['name'])) ?>', <?= (float)$item['price'] ?>, <?= (int)($item['tdp'] ?? 0) ?>)">
                  <div class="item-info">
                    <input type="radio" 
                           name="comp_<?= htmlspecialchars($catKey) ?>" 
                           value="<?= htmlspecialchars($item['id']) ?>" 
                           class="item-radio" 
                           data-tdp="<?= (int)($item['tdp'] ?? 0) ?>"
                           <?= !empty($item['selected']) ? 'checked' : '' ?>>
                    <div class="item-details">
                      <h4><?= htmlspecialchars($item['brand_icon'] ?? '') ?> <?= htmlspecialchars($item['name']) ?></h4>
                      <p><?= htmlspecialchars($item['spec'] ?? '') ?></p>
                    </div>
                  </div>
                  <div class="item-price">RM <?= number_format($item['price'], 2) ?></div>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Right Column: Sticky Live Summary & Actions -->
      <div class="sidebar-column">
        <div class="summary-card">
          <div class="summary-title">
            <span>配置清单概览</span>
            <span style="font-size: 12px; color: #10b981; font-weight: 700;">🟢 兼容性检测通过</span>
          </div>

          <ul class="summary-list" id="summaryList">
            <!-- Dynamically populated via JS -->
          </ul>

          <div class="power-box">
            <div class="power-header">
              <span>预估整机满载功耗 (TDP)</span>
              <span class="power-val" id="powerVal">~ 550W</span>
            </div>
            <div class="power-bar">
              <div class="power-fill" id="powerFill" style="width: 55%;"></div>
            </div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;" id="powerRecText">
              建议搭配 <strong style="color: #fff;">750W 80+ 金牌</strong> 或更高额定功率电源
            </div>
          </div>

          <div class="total-section">
            <span class="total-label">配置总价 (MYR)</span>
            <span class="total-amount" id="totalAmount">RM 0.00</span>
          </div>

          <button class="btn btn-primary" style="width: 100%; justify-content: center; padding: 13px; font-size: 15px; background: #ff334b; color: #fff; border: none; border-radius: 8px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 15px rgba(255,51,75,0.4);" onclick="proceedToCheckout()">
            💳 立即结算并在线支付 (Payment) &rarr;
          </button>

          <button class="btn" style="width: 100%; justify-content: center; padding: 11px; font-size: 13px; background: #161922; color: #00f2fe; border: 1px solid rgba(0,242,254,0.3); border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: 10px;" onclick="openSaveModal()">
            💾 保存配置方案至方案库 (CRUD)
          </button>
          
          <button class="btn" style="width: 100%; justify-content: center; padding: 10px; font-size: 12px; background: #161922; color: #8c93a0; border: 1px solid var(--border-color); border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px;" onclick="copySpecs()">
            📋 复制当前硬件规格文本
          </button>
        </div>
      </div>

    </div>

  </div>

  <!-- Save / Update Build Modal -->
  <div class="gopc-modal-overlay" id="saveBuildModal">
    <div class="gopc-modal">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
        <h3 style="font-size: 17px; font-weight: 800; color: #fff;" id="modalTitle">💾 保存配置到方案库</h3>
        <button style="background: none; border: none; color: #8c93a0; font-size: 18px; cursor: pointer;" onclick="closeSaveModal()">&times;</button>
      </div>

      <form id="saveBuildForm" onsubmit="submitSaveBuild(event)">
        <input type="hidden" id="editBuildId" value="<?= $loadedBuild ? (int)$loadedBuild['BuildID'] : '' ?>">
        
        <div class="form-group">
          <label>方案名称 (Build Name)</label>
          <input type="text" id="modalBuildName" class="form-control" value="<?= htmlspecialchars($loadedBuild['BuildName'] ?? '我的专属电竞战机方案') ?>" required>
        </div>

        <div class="form-group">
          <label>您的称呼 (Customer Name)</label>
          <input type="text" id="modalCustomerName" class="form-control" value="<?= htmlspecialchars($loadedBuild['CustomerName'] ?? '玩家') ?>" required>
        </div>

        <div class="form-group">
          <label>联系电话 / WhatsApp</label>
          <input type="text" id="modalPhone" class="form-control" value="<?= htmlspecialchars($loadedBuild['ContactPhone'] ?? '+60 ') ?>" placeholder="+60 12-345 6789">
        </div>

        <div class="form-group">
          <label>装机要求与备注 (Notes)</label>
          <textarea id="modalNotes" class="form-control" rows="2" placeholder="如：RGB灯效同步、指定白线定制线、需预装 Windows 11 专业版..."><?= htmlspecialchars($loadedBuild['Notes'] ?? '') ?></textarea>
        </div>

        <div style="background: #161922; padding: 12px; border-radius: 8px; margin-bottom: 18px; font-size: 13px; color: #8c93a0;">
          <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
            <span>总计瓦数预估：</span>
            <strong style="color: #fbbf24;" id="modalWattage">550 W</strong>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>方案总价 (MYR)：</span>
            <strong style="color: #00f2fe; font-size: 16px;" id="modalPrice">RM 0.00</strong>
          </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
          <button type="button" class="btn" style="background: #161922; color: #8c93a0; border: 1px solid var(--border-color); padding: 9px 18px; border-radius: 8px; font-weight: 600; cursor: pointer;" onclick="closeSaveModal()">取消</button>
          <button type="submit" class="btn btn-primary" style="background: #ff334b; color: #fff; border: none; padding: 9px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;" id="modalSubmitBtn">确定保存</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Toast Notification -->
  <div id="toast"></div>

  <?php require __DIR__ . '/layout/footer.php'; ?>
  <?php require __DIR__ . '/layout/ai_chat_widget.php'; ?>

  <script>
    const selectedBuild = {};
    const loadedBuildData = <?= $loadedBuild ? json_encode($loadedBuild['components'] ?? []) : 'null' ?>;

    function selectComponent(catKey, id, name, price, tdp) {
      selectedBuild[catKey] = { id, name, price, tdp: tdp || 0 };

      // Update UI classes
      const container = document.querySelector(`.category-card[data-category="${catKey}"]`);
      if (container) {
        container.querySelectorAll('.item-option').forEach(el => el.classList.remove('selected'));
      }

      const radio = document.querySelector(`input[name="comp_${catKey}"][value="${id}"]`);
      if (radio) {
        radio.checked = true;
        const parentOption = radio.closest('.item-option');
        if (parentOption) parentOption.classList.add('selected');
      }

      renderSummary();
    }

    function renderSummary() {
      const summaryList = document.getElementById('summaryList');
      summaryList.innerHTML = '';
      let total = 0;
      let totalTdp = 0;

      for (const [cat, item] of Object.entries(selectedBuild)) {
        total += item.price;
        totalTdp += (item.tdp || 0);

        const li = document.createElement('li');
        li.className = 'summary-item';
        li.innerHTML = `
          <span class="summary-item-label" title="${item.name}">${item.name}</span>
          <span class="summary-item-price">RM ${item.price.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
        `;
        summaryList.appendChild(li);
      }

      // Add base system wattage ~ 60W
      const finalWattage = Math.max(300, totalTdp + 60);

      document.getElementById('totalAmount').textContent = 'RM ' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
      document.getElementById('powerVal').textContent = '~ ' + finalWattage + 'W';

      // Recommended PSU
      let recPsu = 650;
      if (finalWattage > 600) recPsu = 850;
      if (finalWattage > 800) recPsu = 1000;
      document.getElementById('powerRecText').innerHTML = `建议搭配 <strong style="color: #fff;">${recPsu}W 80+ 金牌</strong> 或更高额定功率电源`;

      const powerPercent = Math.min(100, Math.round((finalWattage / 1000) * 100));
      document.getElementById('powerFill').style.width = powerPercent + '%';

      // Update modal info
      document.getElementById('modalWattage').textContent = finalWattage + ' W';
      document.getElementById('modalPrice').textContent = 'RM ' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function applyPreset(preset) {
      showToast(preset.msg || '已加载预设');
      if (preset.build) {
        for (const [cat, id] of Object.entries(preset.build)) {
          const radio = document.querySelector(`input[name="comp_${cat}"][value="${id}"]`);
          if (radio) {
            const card = radio.closest('.category-card');
            const option = radio.closest('.item-option');
            const name = option.querySelector('h4').textContent.trim();
            const priceStr = option.querySelector('.item-price').textContent.replace(/[^\d.]/g, '');
            const tdp = parseInt(radio.getAttribute('data-tdp')) || 0;
            selectComponent(cat, id, name, parseFloat(priceStr), tdp);
          }
        }
      }
    }

    function resetConfig() {
      showToast('已重置所有硬件为默认选项');
      document.querySelectorAll('.category-card').forEach(card => {
        const firstRadio = card.querySelector('.item-radio');
        if (firstRadio) firstRadio.click();
      });
    }

    function showToast(message) {
      const toast = document.getElementById('toast');
      toast.textContent = message;
      toast.style.display = 'block';
      setTimeout(() => {
        toast.style.display = 'none';
      }, 3000);
    }

    function copySpecs() {
      const lines = Object.values(selectedBuild).map(item => `• ${item.name} - RM ${item.price.toFixed(2)}`);
      const text = "【JUSPC 玩家专属电脑组装清单】\n" + lines.join("\n") + "\n总计: " + document.getElementById('totalAmount').textContent;
      navigator.clipboard.writeText(text).then(() => {
        showToast('已成功复制配置单到剪贴板！');
      }).catch(() => {
        showToast('复制完成！');
      });
    }

    function openSaveModal() {
      document.getElementById('saveBuildModal').style.display = 'flex';
    }

    function closeSaveModal() {
      document.getElementById('saveBuildModal').style.display = 'none';
    }

    async function submitSaveBuild(e) {
      e.preventDefault();
      const btn = document.getElementById('modalSubmitBtn');
      btn.disabled = true;
      btn.textContent = '保存中...';

      let total = 0;
      let totalTdp = 0;
      for (const item of Object.values(selectedBuild)) {
        total += item.price;
        totalTdp += (item.tdp || 0);
      }

      const formData = new FormData();
      formData.append('build_id', document.getElementById('editBuildId').value);
      formData.append('build_name', document.getElementById('modalBuildName').value);
      formData.append('customer_name', document.getElementById('modalCustomerName').value);
      formData.append('phone', document.getElementById('modalPhone').value);
      formData.append('notes', document.getElementById('modalNotes').value);
      formData.append('wattage', Math.max(300, totalTdp + 60));
      formData.append('price', total);
      formData.append('components', JSON.stringify(selectedBuild));

      try {
        const res = await fetch('index.php?page=api-save-build', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();
        closeSaveModal();
        showToast(data.message || '配置已成功保存！');
        setTimeout(() => {
          window.location.href = 'index.php?page=saved-builds';
        }, 1000);
      } catch (err) {
        showToast('保存异常，请检查网络！');
      } finally {
        btn.disabled = false;
        btn.textContent = '确定保存';
      }
    }

    function proceedToCheckout() {
      // Store current build in sessionStorage and redirect to payment
      sessionStorage.setItem('juspc_checkout_build', JSON.stringify(selectedBuild));
      window.location.href = 'index.php?page=payment&source=builder';
    }

    // Initialize selections on load
    document.addEventListener('DOMContentLoaded', () => {
      if (loadedBuildData && typeof loadedBuildData === 'object' && Object.keys(loadedBuildData).length > 0) {
        // Load saved build components
        for (const [cat, item] of Object.entries(loadedBuildData)) {
          if (item && item.id) {
            const radio = document.querySelector(`input[name="comp_${cat}"][value="${item.id}"]`);
            if (radio) {
              radio.click();
            }
          }
        }
      } else {
        // Default checked
        document.querySelectorAll('.item-radio:checked').forEach(radio => {
          const option = radio.closest('.item-option');
          const card = radio.closest('.category-card');
          if (option && card) {
            const catKey = card.getAttribute('data-category');
            const id = radio.value;
            const name = option.querySelector('h4').textContent.trim();
            const priceStr = option.querySelector('.item-price').textContent.replace(/[^\d.]/g, '');
            const tdp = parseInt(radio.getAttribute('data-tdp')) || 0;
            selectedBuild[catKey] = { id, name, price: parseFloat(priceStr) || 0, tdp };
          }
        });
      }
      renderSummary();
    });
  </script>
</body>
</html>
