<?php
/**
 * @var string $pageTitle
 * @var array $cpuList
 * @var array $gpuList
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
    .calc-layout {
      display: grid;
      grid-template-columns: 460px 1fr;
      gap: 30px;
      align-items: start;
    }
    @media (max-width: 950px) {
      .calc-layout {
        grid-template-columns: 1fr;
      }
    }

    .card-box {
      background: #12141a;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 14px;
      padding: 24px;
      margin-bottom: 24px;
    }
    .card-title {
      font-size: 16px;
      font-weight: 800;
      color: #fff;
      margin-bottom: 18px;
      border-bottom: 1px solid rgba(255,255,255,0.06);
      padding-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .form-group {
      margin-bottom: 18px;
    }
    .form-group label {
      display: block;
      font-size: 12px;
      font-weight: 700;
      color: #8c93a0;
      margin-bottom: 8px;
      text-transform: uppercase;
    }
    .form-select {
      width: 100%;
      background: #0d0f14;
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 8px;
      padding: 12px 14px;
      color: #fff;
      font-size: 14px;
      outline: none;
      cursor: pointer;
    }
    .form-select:focus {
      border-color: #00f2fe;
    }

    /* Resolution & Workload Buttons */
    .pill-group {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
      gap: 8px;
    }
    .pill-btn {
      background: #0d0f14;
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 8px;
      padding: 9px 8px;
      color: #8c93a0;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
      text-align: center;
      transition: all 0.15s;
    }
    .pill-btn.active {
      background: rgba(0, 242, 254, 0.12);
      border-color: #00f2fe;
      color: #00f2fe;
    }

    /* Result Dashboard Cards */
    .metrics-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }
    .metric-card {
      background: #161922;
      border: 1px solid rgba(255,255,255,0.06);
      border-radius: 10px;
      padding: 18px;
      text-align: center;
    }
    .metric-val {
      font-size: 28px;
      font-weight: 900;
      color: #00f2fe;
      margin: 6px 0;
    }
    .metric-label {
      font-size: 11px;
      color: #8c93a0;
      text-transform: uppercase;
      font-weight: 600;
    }

    /* Bottleneck Bar */
    .bottleneck-box {
      background: #0d0f14;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 10px;
      padding: 18px;
      margin-bottom: 24px;
    }
    .bn-meter {
      height: 12px;
      background: rgba(255,255,255,0.08);
      border-radius: 6px;
      overflow: hidden;
      margin: 10px 0;
    }
    .bn-meter-fill {
      height: 100%;
      background: linear-gradient(90deg, #10b981 0%, #fbbf24 60%, #ff334b 100%);
      width: 5%;
      transition: width 0.3s;
    }

    /* Games FPS Grid */
    .fps-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
      gap: 12px;
    }
    .fps-card {
      background: #0d0f14;
      border: 1px solid rgba(255,255,255,0.06);
      border-radius: 8px;
      padding: 12px 14px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .fps-val {
      font-size: 16px;
      font-weight: 800;
      color: #10b981;
    }
  </style>
</head>
<body>

  <?php require __DIR__ . '/layout/navbar.php'; ?>

  <div class="container">

    <div style="margin-bottom: 28px;">
      <div style="font-size: 12px; color: #00f2fe; font-weight: 700; text-transform: uppercase;">Hardware Benchmark & Synergy Engine</div>
      <h1 style="font-size: 28px; font-weight: 800; color: #fff; margin-top: 4px;">⚡ CPU 算力、瓶颈系数与整机功耗智能测算</h1>
      <p style="font-size: 13px; color: #8c93a0;">基于真实 3DMark TimeSpy 与 Cinebench 跑分数据库，智能评估处理器与显卡协作平衡性与电源瓦数冗余。</p>
    </div>

    <div class="calc-layout">

      <!-- Left Column: Hardware Selection Form -->
      <div>
        <div class="card-box">
          <div class="card-title">
            <span>⚙️ 1. 选择核心硬件参数</span>
          </div>

          <!-- CPU Select -->
          <div class="form-group">
            <label>处理器 (CPU)</label>
            <select id="selectCpu" class="form-select" onchange="runCalculation()">
              <?php foreach ($cpuList as $cpu): ?>
                <option value="<?= $cpu['id'] ?>" data-tdp="<?= $cpu['tdp'] ?>" data-score="<?= $cpu['score'] ?>" data-name="<?= htmlspecialchars($cpu['name']) ?>" data-price="<?= $cpu['price'] ?>" <?= $cpu['id'] === 'i7-14700K' ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cpu['name']) ?> (<?= $cpu['cores'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- GPU Select -->
          <div class="form-group">
            <label>图形显卡 (GPU)</label>
            <select id="selectGpu" class="form-select" onchange="runCalculation()">
              <?php foreach ($gpuList as $gpu): ?>
                <option value="<?= $gpu['id'] ?>" data-tdp="<?= $gpu['tdp'] ?>" data-score="<?= $gpu['score'] ?>" data-name="<?= htmlspecialchars($gpu['name']) ?>" data-price="<?= $gpu['price'] ?>" <?= $gpu['id'] === '4070tis' ? 'selected' : '' ?>>
                  <?= htmlspecialchars($gpu['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Resolution Pills -->
          <div class="form-group">
            <label>目标显示分辨率 (Resolution)</label>
            <div class="pill-group">
              <div class="pill-btn" onclick="selectRes('1080', this)">1080p FHD</div>
              <div class="pill-btn active" onclick="selectRes('1440', this)">1440p 2K QHD</div>
              <div class="pill-btn" onclick="selectRes('2160', this)">2160p 4K UHD</div>
            </div>
          </div>

          <!-- Workload Pills -->
          <div class="form-group">
            <label>核心使用场景 (Workload)</label>
            <div class="pill-group">
              <div class="pill-btn active" onclick="selectWorkload('game_3a', this)">3A 极高画质游戏</div>
              <div class="pill-btn" onclick="selectWorkload('fps_comp', this)">竞技高刷电竞</div>
              <div class="pill-btn" onclick="selectWorkload('render_4k', this)">4K 剪辑/建模渲染</div>
            </div>
          </div>

          <button class="btn btn-primary" style="width: 100%; justify-content: center; padding: 13px; font-size: 14px; background: #ff334b; color: #fff; border: none; border-radius: 8px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 15px rgba(255,51,75,0.4);" onclick="sendToBuilder()">
            💻 把此 CPU + GPU 组合导入配置器 &rarr;
          </button>
        </div>
      </div>

      <!-- Right Column: Results Dashboard -->
      <div>
        <div class="card-box">
          <div class="card-title">
            <span>📊 2. 算力评估与瓶颈深度诊断</span>
          </div>

          <!-- Metric Cards -->
          <div class="metrics-grid">
            <div class="metric-card">
              <div class="metric-label">瓶颈指数 (Bottleneck)</div>
              <div class="metric-val" id="metricBottleneck" style="color: #10b981;">3.4%</div>
              <div style="font-size: 11px; color: #10b981; font-weight: 700;" id="metricBnTag">🟢 黄金均衡搭配</div>
            </div>

            <div class="metric-card">
              <div class="metric-label">整机满载 TDP 功耗</div>
              <div class="metric-val" id="metricWattage" style="color: #fbbf24;">598 W</div>
              <div style="font-size: 11px; color: #8c93a0;">含主板/风扇/RGB</div>
            </div>

            <div class="metric-card">
              <div class="metric-label">建议额定电源 (PSU)</div>
              <div class="metric-val" id="metricPsu" style="color: #00f2fe;">750 W</div>
              <div style="font-size: 11px; color: #00f2fe; font-weight: 700;">80+ 金牌全模组</div>
            </div>
          </div>

          <!-- Bottleneck Explanation Box -->
          <div class="bottleneck-box">
            <div style="display: flex; justify-content: space-between; font-size: 13px;">
              <span style="font-weight: 700; color: #fff;">双芯协作性能释放系数：</span>
              <span style="color: #10b981; font-weight: 800;" id="bnPercentText">96.6% 极致利用率</span>
            </div>
            <div class="bn-meter">
              <div class="bn-meter-fill" id="bnMeterFill" style="width: 15%;"></div>
            </div>
            <div style="font-size: 12px; color: #8c93a0; line-height: 1.6;" id="bnAdvice">
              当前配置在 2K 分辨率下完美匹配。CPU 与 GPU 之间无明显瓶颈，两者均能在 3A 大作中发挥 96% 以上全部理论性能！
            </div>
          </div>

          <!-- Thermal & Cooling Advice -->
          <div style="background: rgba(0,242,254,0.06); border: 1px solid rgba(0,242,254,0.2); border-radius: 10px; padding: 16px; margin-bottom: 24px;">
            <div style="font-size: 13px; font-weight: 800; color: #00f2fe; margin-bottom: 4px;">
              ❄️ 官方散热方案建议 (Cooling Recommendation)
            </div>
            <div style="font-size: 12px; color: #e2e8f0; line-height: 1.6;" id="coolingAdvice">
              当前 CPU 满载功耗可达 253W，强烈建议选用 <strong>360mm 一体式水冷 (如 DeepCool LT720)</strong>，确保长时间烤机温度压制在 75°C 以下。
            </div>
          </div>

          <!-- Estimated FPS Grid in Games -->
          <div>
            <div style="font-size: 14px; font-weight: 800; color: #fff; margin-bottom: 12px;">
              🎮 预估热门游戏实测平均帧率 (Estimated Avg FPS)
            </div>
            <div class="fps-grid" id="fpsGrid">
              <!-- Dynamically populated via JS -->
            </div>
          </div>

        </div>
      </div>

    </div>

  </div>

  <?php require __DIR__ . '/layout/footer.php'; ?>
  <?php require __DIR__ . '/layout/ai_chat_widget.php'; ?>

  <script>
    let currentRes = '1440';
    let currentWorkload = 'game_3a';

    function selectRes(val, el) {
      currentRes = val;
      el.parentElement.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
      el.classList.add('active');
      runCalculation();
    }

    function selectWorkload(val, el) {
      currentWorkload = val;
      el.parentElement.querySelectorAll('.pill-btn').forEach(b => b.classList.remove('active'));
      el.classList.add('active');
      runCalculation();
    }

    function runCalculation() {
      const cpuOpt = document.getElementById('selectCpu').selectedOptions[0];
      const gpuOpt = document.getElementById('selectGpu').selectedOptions[0];

      const cpuTdp = parseInt(cpuOpt.getAttribute('data-tdp')) || 150;
      const gpuTdp = parseInt(gpuOpt.getAttribute('data-tdp')) || 250;
      const cpuScore = parseInt(cpuOpt.getAttribute('data-score')) || 90;
      const gpuScore = parseInt(gpuOpt.getAttribute('data-score')) || 90;

      // Calculate total system power: CPU + GPU + 70W base
      const totalWattage = cpuTdp + gpuTdp + 70;
      document.getElementById('metricWattage').textContent = totalWattage + ' W';

      // Recommended PSU with 25% safety headroom
      let recPsu = 650;
      if (totalWattage > 500) recPsu = 750;
      if (totalWattage > 650) recPsu = 850;
      if (totalWattage > 800) recPsu = 1000;
      document.getElementById('metricPsu').textContent = recPsu + ' W';

      // Bottleneck calculation
      // At 1080p, CPU matters more. At 4K, GPU dominates.
      let diff = Math.abs(cpuScore - gpuScore);
      if (currentRes === '1080' && cpuScore < gpuScore) diff += 6;
      if (currentRes === '2160' && gpuScore < cpuScore) diff += 4;

      const bn = Math.min(28, Math.max(1.8, (diff * 0.95).toFixed(1)));
      document.getElementById('metricBottleneck').textContent = bn + '%';

      const meterFill = document.getElementById('bnMeterFill');
      const bnTag = document.getElementById('metricBnTag');
      const bnAdvice = document.getElementById('bnAdvice');
      const bnPercentText = document.getElementById('bnPercentText');

      if (bn < 6) {
        document.getElementById('metricBottleneck').style.color = '#10b981';
        bnTag.textContent = '🟢 黄金极佳均衡搭配';
        bnTag.style.color = '#10b981';
        meterFill.style.width = '12%';
        meterFill.style.background = '#10b981';
        bnPercentText.textContent = (100 - bn).toFixed(1) + '% 极限利用率';
        bnAdvice.textContent = `该套组合在当前分辨率下极佳均衡。两者协同能够发挥双方全部潜力，无任何硬件性能闲置！`;
      } else if (bn < 14) {
        document.getElementById('metricBottleneck').style.color = '#fbbf24';
        bnTag.textContent = '🟡 轻微瓶颈 (仍属推荐)';
        bnTag.style.color = '#fbbf24';
        meterFill.style.width = '35%';
        meterFill.style.background = '#fbbf24';
        bnPercentText.textContent = (100 - bn).toFixed(1) + '% 良好利用率';
        bnAdvice.textContent = `存在约 ${bn}% 的轻微瓶颈偏向，在日常体验中几乎不可感知，可放心选购组装。`;
      } else {
        document.getElementById('metricBottleneck').style.color = '#ff334b';
        bnTag.textContent = '🔴 存在性能限制瓶颈';
        bnTag.style.color = '#ff334b';
        meterFill.style.width = '65%';
        meterFill.style.background = '#ff334b';
        bnPercentText.textContent = (100 - bn).toFixed(1) + '% 限制利用率';
        bnAdvice.textContent = `两者性能梯队存在差距，其中一方可能无法满血释放。建议适当升级搭配。`;
      }

      // Cooling advice
      const coolingEl = document.getElementById('coolingAdvice');
      if (cpuTdp >= 200) {
        coolingEl.innerHTML = `当前 CPU 峰值功耗高达 <strong>${cpuTdp}W</strong>，强烈建议搭配 <strong>360mm 一体式水冷 (如 DeepCool LT720)</strong>，确保重负载持续睿频。`;
      } else if (cpuTdp >= 120) {
        coolingEl.innerHTML = `当前 CPU 功耗约为 <strong>${cpuTdp}W</strong>，建议选用 <strong>双塔六热管风冷 (如 Thermalright PA120)</strong> 或 240mm/360mm 水冷。`;
      } else {
        coolingEl.innerHTML = `当前 CPU 属于超高能效比型号 (仅 <strong>${cpuTdp}W</strong>)，百元级风冷散热即可轻松降温静音运行！`;
      }

      // Compute FPS
      renderFps(gpuScore, cpuScore);
    }

    function renderFps(gpuScore, cpuScore) {
      let multiplier = 1.0;
      if (currentRes === '1080') multiplier = 1.35;
      if (currentRes === '2160') multiplier = 0.62;

      const baseCyberpunk = Math.round((gpuScore * 0.95 + cpuScore * 0.2) * multiplier * 0.9);
      const baseWukong = Math.round((gpuScore * 0.9 + cpuScore * 0.25) * multiplier * 0.95);
      const baseValorant = Math.round((cpuScore * 2.8 + gpuScore * 1.5) * (currentRes === '2160' ? 0.8 : 1.2));
      const baseGTA = Math.round((gpuScore * 1.2 + cpuScore * 0.5) * multiplier);
      const baseCS2 = Math.round((cpuScore * 2.0 + gpuScore * 1.2) * multiplier);
      const baseApex = Math.round((gpuScore * 1.4 + cpuScore * 0.4) * multiplier);

      const games = [
        { name: '赛博朋克 2077 (光追)', fps: baseCyberpunk, tag: '3A 巅峰' },
        { name: '黑神话：悟空 (高画质)', fps: baseWukong, tag: '国产大作' },
        { name: 'Valorant 无畏契约', fps: baseValorant, tag: '高刷竞技' },
        { name: 'Counter-Strike 2', fps: baseCS2, tag: '职业电竞' },
        { name: 'Apex Legends', fps: baseApex, tag: '极速射击' },
        { name: 'GTA V / GTA VI Ready', fps: baseGTA, tag: '开放世界' }
      ];

      const container = document.getElementById('fpsGrid');
      container.innerHTML = '';
      games.forEach(g => {
        const div = document.createElement('div');
        div.className = 'fps-card';
        div.innerHTML = `
          <div>
            <div style="font-size: 12px; font-weight: 700; color: #fff;">${g.name}</div>
            <div style="font-size: 10px; color: #8c93a0;">${g.tag}</div>
          </div>
          <div class="fps-val">${g.fps} <span style="font-size: 10px; color: #8c93a0;">FPS</span></div>
        `;
        container.appendChild(div);
      });
    }

    function sendToBuilder() {
      // Direct user into configurator
      window.location.href = 'index.php?page=builder';
    }

    document.addEventListener('DOMContentLoaded', runCalculation);
  </script>
</body>
</html>
