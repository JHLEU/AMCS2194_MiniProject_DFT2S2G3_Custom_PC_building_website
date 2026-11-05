<?php
/**
 * @var string $pageTitle
 * @var array $popularItems
 * @var array $presets
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <style>
    body { background: #0a0b0e; color: #fff; font-family: 'Inter', sans-serif; margin: 0; }
    header { height: 70px; background: #12141a; display: flex; align-items: center; justify-content: space-between; padding: 0 40px; border-bottom: 1px solid rgba(255,255,255,0.08); }
    .logo { font-size: 22px; font-weight: 800; color: #fff; text-decoration: none; }
    .logo span { color: #ff334b; }
    nav a { color: #8c93a0; text-decoration: none; margin-left: 20px; font-weight: 600; }
    nav a:hover, nav a.active { color: #fff; }
    
    .hero { text-align: center; padding: 100px 20px; background: radial-gradient(circle, rgba(255,51,75,0.15) 0%, rgba(10,11,14,1) 70%); }
    .hero h1 { font-size: 48px; font-weight: 800; margin-bottom: 16px; }
    .btn-cta { display: inline-block; background: #ff334b; color: #fff; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: 700; margin-top: 20px; box-shadow: 0 4px 15px rgba(255,51,75,0.4); }
    .btn-cta:hover { background: #e0243a; }

    .container { max-width: 1200px; margin: 0 auto; padding: 60px 20px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 30px; }
    .card { background: #12141a; border: 1px solid rgba(255,255,255,0.08); padding: 24px; border-radius: 12px; }
  </style>
</head>
<body>

  <header>
    <a href="index.php?page=home" class="logo">JUS<span>PC</span> BUILDER</a>
    <nav>
      <a href="index.php?page=home" class="active">首页 Home</a>
      <a href="index.php?page=builder">💻 配件组装 (Builder)</a>
      <a href="index.php?page=components">📦 硬件目录 (Components)</a>
    </nav>
  </header>

  <section class="hero">
    <h1>打造属于你的专属电竞主机</h1>
    <p style="color: #8c93a0;">自定义每一个硬件配件，透明化的价格与专业兼容性检测。</p>
    <a href="index.php?page=builder" class="btn-cta">⚡ 立即开始配机</a>
  </section>

  <div class="container">
    <h2>热门推荐硬件</h2>
    <div class="grid">
      <?php foreach ($popularItems as $item): ?>
        <div class="card">
          <span style="color: #ff334b; font-size: 12px; font-weight: 700;"><?= htmlspecialchars($item['category']) ?></span>
          <h3 style="margin: 10px 0;"><?= htmlspecialchars($item['name']) ?></h3>
          <p style="color: #00f2fe; font-weight: 700; font-size: 18px;">RM <?= number_format($item['price']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</body>
</html>