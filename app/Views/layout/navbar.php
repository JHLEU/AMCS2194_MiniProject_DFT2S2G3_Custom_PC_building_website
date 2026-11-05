<?php
/**
 * Unified Storefront Top Navigation Bar
 * @var string $activePage
 */
$currentPage = $activePage ?? ($_GET['page'] ?? 'home');
?>
<!-- Top Unified Navbar -->
<header class="global-navbar">
  <div class="header-left">
    <a href="index.php?page=home" class="logo">GO<span>PC</span> <span style="font-size: 11px; font-weight: 600; color: #8c93a0; margin-left: 4px; letter-spacing: 0;">CUSTOM PC</span></a>
  </div>
  <nav>
    <a href="index.php?page=home" class="<?= $currentPage === 'home' ? 'active' : '' ?>">首页 Home</a>
    <a href="index.php?page=builder" class="<?= $currentPage === 'builder' ? 'active' : '' ?>">💻 配件组装 (Builder)</a>
    <a href="index.php?page=saved-builds" class="<?= $currentPage === 'saved-builds' ? 'active' : '' ?>">📋 我的配置 (Saved)</a>
    <a href="index.php?page=cpu-calc" class="<?= $currentPage === 'cpu-calc' ? 'active' : '' ?>">⚡ CPU 算力测算</a>
    <a href="index.php?page=payment" class="<?= $currentPage === 'payment' ? 'active' : '' ?>">💳 在线支付 (Payment)</a>
    <a href="index.php?page=tracking" class="<?= $currentPage === 'tracking' ? 'active' : '' ?>">📦 订单追踪 (Tracking)</a>
    <a href="index.php?page=reviews" class="<?= $currentPage === 'reviews' ? 'active' : '' ?>">⭐ 谷歌评价 (Reviews)</a>
    <a href="index.php?page=contact" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">📞 联系我们 (Contact)</a>
    <a href="index.php?page=chat" class="<?= $currentPage === 'chat' ? 'active' : '' ?>">💬 AI 智能顾问</a>
    <a href="index.php?route=inventory/dashboard" style="color: #00f2fe;">📊 ERP 后台</a>
  </nav>
  <div class="header-right">
    <div class="nav-actions">
      <a href="https://wa.me/60121112222?text=Hi%20JUSPC,%20I%20would%20like%20to%20inquire%20about%20a%20custom%20PC%20build" target="_blank" class="btn btn-whatsapp" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none; background: #25d366; color: #000; box-shadow: 0 2px 10px rgba(37, 211, 102, 0.3);">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.288.043.088.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.144.39-.086s1.011.477 1.184.564.289.13.332.203c.044.072.044.419-.1.824z"/></svg>
        WhatsApp
      </a>
      <a href="tel:+60121112222" class="btn btn-phone" style="display: inline-flex; align-items: center; gap: 5px; padding: 7px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none; background: #161922; color: #fff; border: 1px solid rgba(255,255,255,0.15);">
        📞 拨打电话
      </a>
      <a href="index.php?page=builder" class="btn btn-primary" style="display: inline-flex; align-items: center; padding: 7px 15px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none; background: #ff334b; color: #fff; box-shadow: 0 4px 15px rgba(255, 51, 75, 0.4); transition: all 0.15s ease;">
        ⚡ 极速配机
      </a>
    </div>
  </div>
</header>
