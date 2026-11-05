<?php
/**
 * @var string $pageTitle
 * @var array $popularItems
 * @var array $presets
 * @var array $reviewStats
 * @var array $topReviews
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
    .hero {
      text-align: center;
      padding: 90px 20px 80px;
      background: radial-gradient(circle at center, rgba(255,51,75,0.18) 0%, rgba(10,11,14,0.98) 70%);
      position: relative;
      overflow: hidden;
    }
    .hero::before {
      content: '';
      position: absolute;
      top: -50px;
      left: 50%;
      transform: translateX(-50%);
      width: 700px;
      height: 350px;
      background: radial-gradient(ellipse, rgba(0,242,254,0.15) 0%, transparent 70%);
      pointer-events: none;
    }
    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255,51,75,0.15);
      border: 1px solid rgba(255,51,75,0.4);
      color: #ff334b;
      padding: 6px 16px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 700;
      margin-bottom: 20px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .hero h1 {
      font-size: 52px;
      font-weight: 900;
      line-height: 1.15;
      margin-bottom: 18px;
      letter-spacing: -0.02em;
    }
    .hero h1 span.gradient-text {
      background: linear-gradient(135deg, #ffffff 0%, #ff334b 50%, #00f2fe 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .hero p {
      color: #8c93a0;
      font-size: 18px;
      max-width: 780px;
      margin: 0 auto 30px;
      line-height: 1.6;
    }
    .hero-buttons {
      display: flex;
      justify-content: center;
      gap: 16px;
      flex-wrap: wrap;
    }
    .btn-hero {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 14px 32px;
      border-radius: 10px;
      font-size: 15px;
      font-weight: 800;
      text-decoration: none;
      transition: all 0.2s ease;
    }
    .btn-hero-primary {
      background: #ff334b;
      color: #fff;
      box-shadow: 0 4px 20px rgba(255,51,75,0.45);
    }
    .btn-hero-primary:hover {
      background: #e0243a;
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(255,51,75,0.6);
    }
    .btn-hero-secondary {
      background: #161922;
      color: #fff;
      border: 1px solid rgba(255,255,255,0.15);
    }
    .btn-hero-secondary:hover {
      border-color: #00f2fe;
      color: #00f2fe;
      transform: translateY(-2px);
    }

    /* Trust Stats Strip */
    .trust-strip {
      background: #12141a;
      border-top: 1px solid rgba(255,255,255,0.08);
      border-bottom: 1px solid rgba(255,255,255,0.08);
      padding: 24px 20px;
    }
    .trust-container {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      text-align: center;
    }
    .trust-item h3 {
      font-size: 26px;
      font-weight: 800;
      color: #fff;
      margin-bottom: 4px;
    }
    .trust-item p {
      font-size: 12px;
      color: #8c93a0;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    /* Container */
    .container {
      max-width: 1240px;
      margin: 0 auto;
      padding: 60px 20px;
    }
    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-bottom: 30px;
      flex-wrap: wrap;
      gap: 15px;
    }
    .section-title {
      font-size: 28px;
      font-weight: 800;
      color: #fff;
    }
    .section-subtitle {
      font-size: 14px;
      color: #8c93a0;
      margin-top: 4px;
    }

    /* Quick Tracker Banner */
    .track-banner {
      background: linear-gradient(135deg, #161922 0%, #1a1e2b 100%);
      border: 1px solid rgba(0, 242, 254, 0.3);
      box-shadow: 0 10px 30px rgba(0,0,0,0.4);
      border-radius: 14px;
      padding: 28px 32px;
      margin-bottom: 50px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 20px;
    }
    .track-form {
      display: flex;
      gap: 10px;
      flex: 1;
      max-width: 500px;
    }
    .track-input {
      flex: 1;
      background: #0d0f14;
      border: 1px solid rgba(255,255,255,0.15);
      color: #fff;
      padding: 12px 18px;
      border-radius: 8px;
      font-size: 14px;
      outline: none;
    }
    .track-input:focus {
      border-color: #00f2fe;
    }
    .track-btn {
      background: #00f2fe;
      color: #000;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 800;
      cursor: pointer;
      transition: all 0.15s;
      white-space: nowrap;
    }
    .track-btn:hover {
      background: #38f8ff;
      transform: translateY(-1px);
    }

    /* Features Grid */
    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 22px;
      margin-bottom: 60px;
    }
    .feature-card {
      background: #12141a;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 12px;
      padding: 26px;
      transition: all 0.2s ease;
      text-decoration: none;
      color: inherit;
      display: flex;
      flex-direction: column;
    }
    .feature-card:hover {
      border-color: #ff334b;
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(255,51,75,0.15);
    }
    .feature-icon {
      font-size: 32px;
      margin-bottom: 16px;
    }
    .feature-card h3 {
      font-size: 18px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 8px;
    }
    .feature-card p {
      font-size: 13px;
      color: #8c93a0;
      line-height: 1.6;
      flex: 1;
    }
    .feature-link {
      color: #00f2fe;
      font-size: 13px;
      font-weight: 700;
      margin-top: 16px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    /* Hardware Cards Grid */
    .hw-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 20px;
      margin-bottom: 60px;
    }
    .hw-card {
      background: #12141a;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 12px;
      padding: 22px;
      transition: border-color 0.2s;
      position: relative;
    }
    .hw-card:hover {
      border-color: rgba(255,255,255,0.2);
    }
    .hw-tag {
      position: absolute;
      top: 16px;
      right: 16px;
      font-size: 11px;
      font-weight: 700;
      color: #ff334b;
      background: rgba(255,51,75,0.12);
      border: 1px solid rgba(255,51,75,0.3);
      padding: 3px 8px;
      border-radius: 4px;
    }

    /* Reviews Showcase */
    .reviews-box {
      background: #12141a;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 14px;
      padding: 32px;
    }
    .reviews-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 18px;
      margin-top: 24px;
    }
    .rev-card {
      background: #161922;
      border: 1px solid rgba(255,255,255,0.06);
      border-radius: 10px;
      padding: 20px;
    }
  </style>
</head>
<body>

  <?php require __DIR__ . '/layout/navbar.php'; ?>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-badge">⚡ NEXT-GEN CUSTOM PC CONFIGURATOR</div>
    <h1>打造属于你的<span class="gradient-text">专属电竞旗舰主机</span></h1>
    <p>全马首创全线配件兼容性智能检测与 CPU/GPU 算力瓶颈测算。透明官方定价、严苛烤机温控认证，让每颗芯片发挥 100% 极限实力。</p>
    <div class="hero-buttons">
      <a href="index.php?page=builder" class="btn-hero btn-hero-primary">💻 立即开始 3D 配件组装</a>
      <a href="index.php?page=cpu-calc" class="btn-hero btn-hero-secondary">⚡ CPU 算力与功耗测算</a>
      <a href="https://wa.me/60121112222?text=Hi%20JUSPC,%20I%20want%20to%20consult%20about%20a%20PC%20build" target="_blank" class="btn-hero" style="background: #25d366; color: #000;">
        💬 WhatsApp 官方直连
      </a>
    </div>
  </section>

  <!-- Trust Strip -->
  <div class="trust-strip">
    <div class="trust-container">
      <div class="trust-item">
        <h3>3,800+</h3>
        <p>台主机成功交付上门</p>
      </div>
      <div class="trust-item">
        <h3 style="color: #ff334b;">4.9 / 5.0 ⭐</h3>
        <p>Google Verified 权威评分</p>
      </div>
      <div class="trust-item">
        <h3 style="color: #00f2fe;">3 年整机联保</h3>
        <p>原厂行货 保修无忧</p>
      </div>
      <div class="trust-item">
        <h3 style="color: #10b981;">100% 兼容保证</h3>
        <p>出厂 3DMark 烤机压力测试</p>
      </div>
    </div>
  </div>

  <div class="container">

    <!-- Quick Order Tracking Search Banner -->
    <div class="track-banner">
      <div>
        <div style="font-size: 12px; color: #00f2fe; font-weight: 700; text-transform: uppercase;">Real-Time Order Tracking</div>
        <h3 style="font-size: 20px; font-weight: 800; color: #fff; margin-top: 4px;">随时查询你的装机流水与发货动态</h3>
        <p style="font-size: 13px; color: #8c93a0;">输入订单编号或电话号码，即时查阅工程师组装进度、烤机报告与 J&T 物流单号。</p>
      </div>
      <form class="track-form" action="index.php" method="GET">
        <input type="hidden" name="page" value="tracking">
        <input type="text" name="order_no" class="track-input" placeholder="输入订单号，例如 ORD-20261101-8891" required>
        <button type="submit" class="track-btn">立即追踪 &rarr;</button>
      </form>
    </div>

    <!-- Core Features Grid -->
    <div class="section-header">
      <div>
        <h2 class="section-title">全方位高端装机系统功能</h2>
        <p class="section-subtitle">满足从小白到极客玩家的定制组装、算力评估、无忧支付与全程售后追踪</p>
      </div>
    </div>

    <div class="features-grid">
      <!-- Feature 1: Custom Builder & Saved Builds -->
      <a href="index.php?page=builder" class="feature-card">
        <div class="feature-icon">💻</div>
        <h3>配件组装与配置库 (CRUD)</h3>
        <p>自由选配 CPU、显卡、主板等核心硬件。支持实时整机 TDP 功耗监控，并将专属配机方案保存至配置库（创建、修改、保存、直接下单）。</p>
        <div class="feature-link">进入配件组装 &rarr;</div>
      </a>

      <!-- Feature 2: CPU Calculation -->
      <a href="index.php?page=cpu-calc" class="feature-card">
        <div class="feature-icon">⚡</div>
        <h3>CPU 算力与功耗测算工具</h3>
        <p>专业评估 CPU 与 GPU 的瓶颈系数、极限 TDP 功耗、额定电源与散热器需求，预估主流 3A 大作在 1080p/2K/4K 下的真实游戏帧率。</p>
        <div class="feature-link">开始算力测算 &rarr;</div>
      </a>

      <!-- Feature 3: Payment Gateway -->
      <a href="index.php?page=payment" class="feature-card">
        <div class="feature-icon">💳</div>
        <h3>在线结账与支付 (Demo / API)</h3>
        <p>支持 FPX 在线网银 (Maybank, CIMB 等)、信用卡/借记卡、Touch 'n Go eWallet 及 DuitNow 扫码，即时生成电子发票与订单流水。</p>
        <div class="feature-link">体验支付系统 &rarr;</div>
      </a>

      <!-- Feature 4: Order Tracking -->
      <a href="index.php?page=tracking" class="feature-card">
        <div class="feature-icon">📦</div>
        <h3>订单流水追踪 (CRUD)</h3>
        <p>全流程透明可视：付款确认 &rarr; 配件分拣 &rarr; 专业技师装机走线 &rarr; 烤机压力测试 &rarr; 物流快运发货。支持随时修改收件地址与备注。</p>
        <div class="feature-link">查看装机追踪 &rarr;</div>
      </a>

      <!-- Feature 5: Google Reviews -->
      <a href="index.php?page=reviews" class="feature-card">
        <div class="feature-icon">⭐</div>
        <h3>Google 真实口碑评价 (CRUD)</h3>
        <p>4.9 高分好评见证！查看真实客户装机体验、配置分享与点赞，用户可在线发表、编辑或删除个人评价与装机晒单。</p>
        <div class="feature-link">浏览顾客评价 &rarr;</div>
      </a>

      <!-- Feature 6: Contact Us & WhatsApp -->
      <a href="index.php?page=contact" class="feature-card">
        <div class="feature-icon">📞</div>
        <h3>联系我们 (Call / WhatsApp)</h3>
        <p>吉隆坡 Plaza Low Yat 实体展厅恭候品鉴。提供一键拨打电话、WhatsApp 客服即刻交谈，以及在线售后工单提报服务。</p>
        <div class="feature-link">联络客服展厅 &rarr;</div>
      </a>
    </div>

    <!-- Featured Hardware Section -->
    <div class="section-header">
      <div>
        <h2 class="section-title">🔥 热门推荐核心硬件</h2>
        <p class="section-subtitle">精选一线大厂旗舰级 CPU、显卡与水冷，原厂正品现货直供</p>
      </div>
      <a href="index.php?page=components" style="color: #ff334b; font-size: 14px; font-weight: 700; text-decoration: none;">查看全部硬件产品 &rarr;</a>
    </div>

    <div class="hw-grid">
      <?php foreach ($popularItems as $item): ?>
        <div class="hw-card">
          <span class="hw-tag"><?= htmlspecialchars($item['tag'] ?? '热卖现货') ?></span>
          <div style="font-size: 12px; color: #8c93a0; font-weight: 700; margin-bottom: 6px;"><?= htmlspecialchars($item['category']) ?></div>
          <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 12px;"><?= htmlspecialchars($item['name']) ?></h3>
          <div style="display: flex; justify-content: space-between; align-items: baseline;">
            <div style="color: #00f2fe; font-weight: 800; font-size: 20px;">RM <?= number_format($item['price'], 2) ?></div>
            <a href="index.php?page=builder" style="font-size: 12px; color: #ff334b; font-weight: 700; text-decoration: none;">选用此配件 &rarr;</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Google Reviews Section Preview -->
    <div class="reviews-box">
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 24px;">⭐</span>
            <span style="font-size: 24px; font-weight: 800; color: #fff;"><?= $reviewStats['avg_rating'] ?> / 5.0</span>
            <span style="background: #10b981; color: #000; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 4px;">GOOGLE VERIFIED</span>
          </div>
          <div style="font-size: 13px; color: #8c93a0; margin-top: 4px;">基于 <?= $reviewStats['total'] ?>+ 位马来西亚真实装机玩家的 Google 综合评分</div>
        </div>
        <div style="display: flex; gap: 10px;">
          <a href="index.php?page=reviews" class="btn" style="background: #161922; color: #fff; border: 1px solid rgba(255,255,255,0.15); padding: 9px 18px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 13px;">查看全部评价</a>
          <a href="index.php?page=reviews&action=create" class="btn" style="background: #ff334b; color: #fff; padding: 9px 18px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 13px;">✍️ 发表 Google 评价</a>
        </div>
      </div>

      <div class="reviews-grid">
        <?php foreach ($topReviews as $rev): ?>
          <div class="rev-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
              <div style="font-weight: 700; color: #fff; font-size: 14px;"><?= htmlspecialchars($rev['author_name']) ?></div>
              <div style="color: #fbbf24; font-size: 13px;">⭐⭐⭐⭐⭐</div>
            </div>
            <div style="font-size: 11px; color: #00f2fe; margin-bottom: 8px;">已定制：<?= htmlspecialchars($rev['specs_built'] ?? 'Custom Rig') ?></div>
            <p style="font-size: 12px; color: #8c93a0; line-height: 1.6;">"<?= htmlspecialchars($rev['review_text']) ?>"</p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>

  <?php require __DIR__ . '/layout/footer.php'; ?>
  <?php require __DIR__ . '/layout/ai_chat_widget.php'; ?>

</body>
</html>
