<?php
/**
 * @var string $pageTitle
 * @var array $builds
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
      padding: 40px 20px 80px;
      flex: 1;
    }
    .header-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      flex-wrap: wrap;
      gap: 16px;
    }
    .builds-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 22px;
    }
    .build-card {
      background: #12141a;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 12px;
      padding: 24px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: all 0.2s;
    }
    .build-card:hover {
      border-color: rgba(255,51,75,0.4);
      box-shadow: 0 8px 24px rgba(0,0,0,0.5);
    }
    .build-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 12px;
    }
    .build-title {
      font-size: 18px;
      font-weight: 800;
      color: #fff;
    }
    .build-meta {
      font-size: 12px;
      color: #8c93a0;
      margin-top: 4px;
    }
    .parts-list {
      list-style: none;
      padding: 0;
      margin: 16px 0;
      display: flex;
      flex-direction: column;
      gap: 8px;
      background: #0d0f14;
      border: 1px solid rgba(255,255,255,0.05);
      border-radius: 8px;
      padding: 12px 14px;
      max-height: 200px;
      overflow-y: auto;
    }
    .part-item {
      font-size: 12px;
      color: #e2e8f0;
      display: flex;
      justify-content: space-between;
    }
    .part-name {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 220px;
    }
    .build-footer {
      border-top: 1px solid rgba(255,255,255,0.08);
      padding-top: 16px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
    }
    .build-price {
      font-size: 22px;
      font-weight: 800;
      color: #00f2fe;
    }
    .action-btns {
      display: flex;
      gap: 8px;
    }
    .btn-action {
      padding: 8px 14px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 700;
      text-decoration: none;
      cursor: pointer;
      border: none;
      transition: all 0.15s;
    }
    .btn-load {
      background: #ff334b;
      color: #fff;
    }
    .btn-load:hover {
      background: #e0243a;
    }
    .btn-pay {
      background: #00f2fe;
      color: #000;
    }
    .btn-pay:hover {
      background: #38f8ff;
    }
    .btn-del {
      background: #161922;
      color: #8c93a0;
      border: 1px solid rgba(255,255,255,0.1);
    }
    .btn-del:hover {
      color: #ff334b;
      border-color: #ff334b;
    }
  </style>
</head>
<body>

  <?php require __DIR__ . '/layout/navbar.php'; ?>

  <div class="container">

    <div class="header-bar">
      <div>
        <div style="font-size: 12px; color: #ff334b; font-weight: 700; text-transform: uppercase;">Custom Build Management (CRUD)</div>
        <h1 style="font-size: 28px; font-weight: 800; color: #fff; margin-top: 4px;">我的专属 PC 配置方案库</h1>
        <p style="font-size: 13px; color: #8c93a0;">您可以在此查看、加载再编辑、下单结账或删除所有保存的个性化硬件选配方案。</p>
      </div>
      <div>
        <a href="index.php?page=builder" class="btn" style="background: #ff334b; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 13px; box-shadow: 0 4px 15px rgba(255,51,75,0.4);">
          ⚡ 新增定制配机方案 +
        </a>
      </div>
    </div>

    <?php if (empty($builds)): ?>
      <div style="background: #12141a; border: 1px dashed rgba(255,255,255,0.15); border-radius: 12px; text-align: center; padding: 80px 20px;">
        <div style="font-size: 48px; margin-bottom: 16px;">💻</div>
        <h3 style="font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 8px;">暂无保存的配置方案</h3>
        <p style="font-size: 13px; color: #8c93a0; max-width: 450px; margin: 0 auto 24px;">进入配件组装器挑选心仪的 CPU 与显卡，点击“保存配置方案”即可在此统一管理与下单！</p>
        <a href="index.php?page=builder" class="btn" style="background: #ff334b; color: #fff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 700;">
          ⚡ 立即进入配件组装器
        </a>
      </div>
    <?php else: ?>
      <div class="builds-grid">
        <?php foreach ($builds as $b): ?>
          <div class="build-card" id="buildCard_<?= $b['BuildID'] ?>">
            <div>
              <div class="build-header">
                <div>
                  <div class="build-title"><?= htmlspecialchars($b['BuildName'] ?? '专属装机方案') ?></div>
                  <div class="build-meta">
                    👤 <?= htmlspecialchars($b['CustomerName'] ?? '玩家') ?> &bull; 
                    📞 <?= htmlspecialchars($b['ContactPhone'] ?? '未填写') ?>
                  </div>
                </div>
                <span style="background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.3); color: #fbbf24; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px;">
                  ~ <?= (int)$b['TotalWattage'] ?>W
                </span>
              </div>

              <?php if (!empty($b['Notes'])): ?>
                <div style="font-size: 12px; color: #8c93a0; background: rgba(255,255,255,0.03); padding: 6px 10px; border-radius: 6px; margin-bottom: 8px;">
                  💬 <strong>备注：</strong> <?= htmlspecialchars($b['Notes']) ?>
                </div>
              <?php endif; ?>

              <!-- Parts summary -->
              <ul class="parts-list">
                <?php if (!empty($b['components'])): ?>
                  <?php foreach ($b['components'] as $catKey => $comp): ?>
                    <li class="part-item">
                      <span class="part-name">🔹 <?= htmlspecialchars($comp['name'] ?? $catKey) ?></span>
                      <span style="color: #8c93a0;">RM <?= number_format($comp['price'] ?? 0, 2) ?></span>
                    </li>
                  <?php endforeach; ?>
                <?php else: ?>
                  <li style="color: #5a6270; font-size: 11px;">未记录详细配件清单</li>
                <?php endif; ?>
              </ul>
            </div>

            <div>
              <div style="font-size: 11px; color: #5a6270; margin-bottom: 4px;">创建时间：<?= htmlspecialchars($b['CreatedAt'] ?? '') ?></div>
              <div class="build-footer">
                <div>
                  <div style="font-size: 11px; color: #8c93a0;">方案总价</div>
                  <div class="build-price">RM <?= number_format((float)$b['EstimatedPrice'], 2) ?></div>
                </div>
                <div class="action-btns">
                  <!-- Load & Edit in Configurator (CRUD - Update) -->
                  <a href="index.php?page=builder&load_id=<?= $b['BuildID'] ?>" class="btn-action btn-load" title="加载配置并在组装器中修改">
                    ✏️ 编辑
                  </a>
                  <!-- Direct Checkout Payment -->
                  <a href="index.php?page=payment&build_id=<?= $b['BuildID'] ?>" class="btn-action btn-pay" title="立即为该配置下单结账">
                    💳 下单
                  </a>
                  <!-- Delete Build (CRUD - Delete) -->
                  <button onclick="deleteBuild(<?= $b['BuildID'] ?>)" class="btn-action btn-del" title="删除该方案">
                    🗑️
                  </button>
                </div>
              </div>
            </div>

          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>

  <?php require __DIR__ . '/layout/footer.php'; ?>
  <?php require __DIR__ . '/layout/ai_chat_widget.php'; ?>

  <script>
    async function deleteBuild(id) {
      if (!confirm('确定彻底删除该配置方案吗？此操作无法撤销。')) return;

      try {
        const formData = new FormData();
        formData.append('id', id);

        const res = await fetch('index.php?page=api-delete-build', {
          method: 'POST',
          headers: { 'Accept': 'application/json' },
          body: formData
        });

        const card = document.getElementById('buildCard_' + id);
        if (card) {
          card.style.opacity = '0.3';
          card.style.transform = 'scale(0.95)';
          setTimeout(() => card.remove(), 250);
        }
      } catch(e) {
        alert('删除失败，请稍后重试');
      }
    }
  </script>
</body>
</html>
