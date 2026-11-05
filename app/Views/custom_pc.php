<!-- 顶部导航栏 (已加入页面跳转菜单) -->
<header class="navbar">
  <a href="index.php?page=home" class="logo">JUS<span>PC</span> BUILDER</a>
  <div style="display: flex; gap: 20px;">
    <a href="index.php?page=home" style="color: var(--text-muted); text-decoration: none; font-weight: 600;">首页 Home</a>
    <a href="index.php?page=builder" style="color: #fff; text-decoration: none; font-weight: 600;">💻 配件组装</a>
    <a href="index.php?page=components" style="color: var(--text-muted); text-decoration: none; font-weight: 600;">📦 硬件目录</a>
  </div>
  <div class="nav-actions">
    <button class="btn btn-secondary" onclick="showToast('已重置所有配置选项')">重置配置</button>
    <button class="btn btn-primary" onclick="showToast('正在保存当前配置...')">保存配置</button>
  </div>
</header>