<?php
/**
 * @var string $pageTitle
 * @var array $components
 * @var array $presets
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'JUSPC Custom PC Configurator') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg-body: #0a0b0e;
      --bg-card: #12141a;
      --bg-surface: #161922;
      --border-color: rgba(255, 255, 255, 0.08);
      --primary: #ff334b;
      --primary-hover: #e0243a;
      --accent-cyan: #00f2fe;
      --text-main: #ffffff;
      --text-muted: #8c93a0;
      --glow-crimson: 0 4px 15px rgba(255, 51, 75, 0.4);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: var(--bg-body);
      color: var(--text-main);
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      background-image: radial-gradient(circle at 80% 0%, rgba(255, 51, 75, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 10% 100%, rgba(0, 242, 254, 0.04) 0%, transparent 40%);
      background-attachment: fixed;
    }

    /* Header */
    header {
      height: 70px;
      background: #12141a;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 40px;
      border-bottom: 1px solid var(--border-color);
      position: sticky;
      top: 0;
      z-index: 100;
      backdrop-filter: blur(10px);
    }
    .header-left { flex: 1; display: flex; align-items: center; justify-content: flex-start; }
    .logo { font-size: 22px; font-weight: 800; color: #fff; text-decoration: none; letter-spacing: -0.02em; white-space: nowrap; }
    .logo span { color: var(--primary); }
    nav { flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
    nav a { color: var(--text-muted); text-decoration: none; margin: 0 12px; font-weight: 600; font-size: 14px; transition: color 0.15s; white-space: nowrap; }
    nav a:hover, nav a.active { color: #fff; }
    .header-right { flex: 1; display: flex; align-items: center; justify-content: flex-end; }
    .nav-actions { display: flex; gap: 12px; align-items: center; }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 9px 18px;
      border-radius: 8px;
      font-weight: 700;
      font-size: 13px;
      cursor: pointer;
      text-decoration: none;
      border: 1px solid transparent;
      transition: all 0.15s ease;
      font-family: inherit;
    }
    .btn-primary {
      background: var(--primary);
      color: #fff;
      box-shadow: var(--glow-crimson);
    }
    .btn-primary:hover {
      background: var(--primary-hover);
      transform: translateY(-1px);
    }
    .btn-secondary {
      background: var(--bg-surface);
      color: var(--text-muted);
      border-color: var(--border-color);
    }
    .btn-secondary:hover {
      background: rgba(255, 255, 255, 0.06);
      color: #fff;
    }
    .btn-cyan {
      background: var(--accent-cyan);
      color: #000;
      font-weight: 800;
    }
    .btn-cyan:hover {
      background: #38f8ff;
      transform: translateY(-1px);
    }

    /* Container & Layout */
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
      grid-template-columns: 1fr 380px;
      gap: 28px;
      align-items: start;
    }

    @media (max-width: 992px) {
      .builder-layout {
        grid-template-columns: 1fr;
      }
    }

    /* Component Sections */
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

    /* Sticky Sidebar Summary */
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
      gap: 12px;
      margin-bottom: 24px;
    }
    .summary-item {
      display: flex;
      justify-content: space-between;
      font-size: 13px;
    }
    .summary-item-label {
      color: var(--text-muted);
      max-width: 200px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .summary-item-price {
      font-weight: 700;
      color: #fff;
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
    }

    .total-section {
      border-top: 1px solid var(--border-color);
      padding-top: 18px;
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
      font-size: 28px;
      font-weight: 800;
      color: var(--accent-cyan);
      letter-spacing: -0.02em;
    }

    /* Toast */
    #toast {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: #161922;
      border: 1px solid var(--primary);
      box-shadow: 0 10px 30px rgba(0,0,0,0.6), 0 0 15px rgba(255, 51, 75, 0.4);
      color: #fff;
      padding: 14px 22px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      display: none;
      z-index: 1000;
      animation: fadeIn 0.2s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

  <!-- Top Navbar -->
  <header>
    <div class="header-left">
      <a href="index.php?page=home" class="logo">JUS<span>PC</span> BUILDER</a>
    </div>
    <nav>
      <a href="index.php?page=home">首页 Home</a>
      <a href="index.php?page=builder" class="active">💻 配件组装 (Builder)</a>
      <a href="index.php?page=components">📦 硬件目录 (Components)</a>
      <a href="/index.php?route=inventory/dashboard" style="color: #00f2fe;">📊 ERP 后台 (Admin)</a>
    </nav>
    <div class="header-right">
      <div class="nav-actions">
        <button class="btn btn-secondary" onclick="resetConfig()">重置配置</button>
        <button class="btn btn-primary" onclick="showToast('正在保存当前专属 PC 配置清单...')">保存配置</button>
      </div>
    </div>
  </header>

  <div class="container">
    <!-- Quick Presets -->
    <div class="preset-bar">
      <div class="preset-title">⚡ 快速配置推荐 (Presets):</div>
      <div class="preset-pills">
        <?php foreach ($presets as $idx => $preset): ?>
          <button class="preset-btn" onclick="applyPreset('<?= htmlspecialchars($preset['msg']) ?>')">
            <?= htmlspecialchars($preset['name']) ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Builder Two-Column Layout -->
    <div class="builder-layout">
      <!-- Left: Components Selector -->
      <div class="components-column">
        <?php foreach ($components as $catKey => $category): ?>
          <div class="category-card" data-category="<?= htmlspecialchars($catKey) ?>">
            <div class="category-header">
              <div class="category-title">
                <span><?= $category['icon'] ?? '⚙️' ?></span>
                <span><?= htmlspecialchars($category['title'] ?? ucfirst($catKey)) ?></span>
              </div>
              <span style="font-size: 12px; color: var(--text-muted);">可选 1 款</span>
            </div>
            <div class="items-list">
              <?php foreach ($category['items'] as $item): ?>
                <label class="item-option <?= !empty($item['selected']) ? 'selected' : '' ?>" 
                       onclick="selectComponent('<?= htmlspecialchars($catKey) ?>', '<?= htmlspecialchars($item['id']) ?>', '<?= htmlspecialchars($item['name']) ?>', <?= (float)$item['price'] ?>)">
                  <div class="item-info">
                    <input type="radio" 
                           name="comp_<?= htmlspecialchars($catKey) ?>" 
                           value="<?= htmlspecialchars($item['id']) ?>" 
                           class="item-radio" 
                           <?= !empty($item['selected']) ? 'checked' : '' ?>>
                    <div class="item-details">
                      <h4><?= htmlspecialchars($item['brand_icon'] ?? '') ?> <?= htmlspecialchars($item['name']) ?></h4>
                      <p><?= htmlspecialchars($item['spec'] ?? '') ?></p>
                    </div>
                  </div>
                  <div class="item-price">RM <?= number_format($item['price']) ?></div>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Right: Live Build Summary Sidebar -->
      <div class="sidebar-column">
        <div class="summary-card">
          <div class="summary-title">
            <span>配置清单概览</span>
            <span style="font-size: 12px; color: #10b981; font-weight: 700;">🟢 兼容性通过</span>
          </div>

          <ul class="summary-list" id="summaryList">
            <!-- Dynamically populated via JS -->
          </ul>

          <div class="power-box">
            <div class="power-header">
              <span>预估整机功耗 (TDP)</span>
              <span class="power-val" id="powerVal">~ 480W</span>
            </div>
            <div class="power-bar">
              <div class="power-fill" id="powerFill"></div>
            </div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">
              建议选用 <strong style="color: #fff;">650W</strong> 或更高额定功率电源
            </div>
          </div>

          <div class="total-section">
            <span class="total-label">配置总价 (MYR)</span>
            <span class="total-amount" id="totalAmount">RM 0</span>
          </div>

          <button class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 15px;" onclick="showToast('🎉 正在为您提交并生成专属组装采购单！')">
            🛒 立即提交并下单配置
          </button>
          
          <button class="btn btn-secondary" style="width: 100%; justify-content: center; margin-top: 10px;" onclick="copySpecs()">
            📋 复制当前硬件规格清单
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast Notification -->
  <div id="toast"></div>

  <script>
    // State of selected components
    const selectedBuild = {};

    function selectComponent(catKey, id, name, price) {
      selectedBuild[catKey] = { id, name, price };
      
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

      for (const [cat, item] of Object.entries(selectedBuild)) {
        total += item.price;
        const li = document.createElement('li');
        li.className = 'summary-item';
        li.innerHTML = `
          <span class="summary-item-label" title="${item.name}">${item.name}</span>
          <span class="summary-item-price">RM ${item.price.toLocaleString()}</span>
        `;
        summaryList.appendChild(li);
      }

      document.getElementById('totalAmount').textContent = 'RM ' + total.toLocaleString();
    }

    function applyPreset(msg) {
      showToast(msg);
      // Auto trigger selection
      renderSummary();
    }

    function resetConfig() {
      showToast('已重置所有硬件选项');
      document.querySelectorAll('.item-radio').forEach((radio, index) => {
        if (index % 2 === 0) {
          radio.click();
        }
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
      const lines = Object.values(selectedBuild).map(item => `• ${item.name} - RM ${item.price.toLocaleString()}`);
      const text = "【JUSPC 玩家专属电脑组装清单】\n" + lines.join("\n") + "\n总计: " + document.getElementById('totalAmount').textContent;
      navigator.clipboard.writeText(text).then(() => {
        showToast('已复制配置单到剪贴板！');
      }).catch(() => {
        showToast('复制完成！');
      });
    }

    // Initialize with default checked items
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.item-radio:checked').forEach(radio => {
        const option = radio.closest('.item-option');
        const card = radio.closest('.category-card');
        if (option && card) {
          const catKey = card.getAttribute('data-category');
          const id = radio.value;
          const name = option.querySelector('h4').textContent.trim();
          const priceStr = option.querySelector('.item-price').textContent.replace(/[^\d.]/g, '');
          selectedBuild[catKey] = { id, name, price: parseFloat(priceStr) || 0 };
        }
      });
      renderSummary();
    });
  </script>
</body>
</html>
