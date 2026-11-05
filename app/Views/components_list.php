<?php
/**
 * @var string $pageTitle
 * @var array $components
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

    .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
    .section-title { margin-bottom: 24px; font-size: 24px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; margin-bottom: 40px; }
    .item-card { background: #12141a; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center; }
  </style>
</head>
<body>

  <header>
    <a href="index.php?page=home" class="logo">JUS<span>PC</span> BUILDER</a>
    <nav>
      <a href="index.php?page=home">首页 Home</a>
      <a href="index.php?page=builder">💻 配件组装 (Builder)</a>
      <a href="index.php?page=components" class="active">📦 硬件目录 (Components)</a>
    </nav>
  </header>

  <div class="container">
    <h1>全线硬件产品目录</h1>
    
    <?php foreach ($components as $cat): ?>
      <h2 class="section-title"><?= $cat['icon'] ?> <?= htmlspecialchars($cat['title']) ?></h2>
      <div class="grid">
        <?php foreach ($cat['items'] as $item): ?>
          <div class="item-card">
            <div>
              <div style="font-weight: 700; margin-bottom: 4px;"><?= htmlspecialchars($item['name']) ?></div>
              <div style="color: #8c93a0; font-size: 12px;"><?= htmlspecialchars($item['spec']) ?></div>
            </div>
            <div style="color: #00f2fe; font-weight: 700;">RM <?= number_format($item['price']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>

</body>
</html>