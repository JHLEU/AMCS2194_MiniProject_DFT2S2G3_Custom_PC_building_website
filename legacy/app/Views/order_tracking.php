<?php
/**
 * @var string $pageTitle
 * @var string $orderNo
 * @var array|null $trackedOrder
 * @var bool $searched
 * @var array $allOrders
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

    /* Search Bar */
    .search-box {
      background: #12141a;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 14px;
      padding: 24px;
      margin-bottom: 30px;
      display: flex;
      gap: 12px;
      align-items: center;
      flex-wrap: wrap;
    }
    .search-input {
      flex: 1;
      min-width: 280px;
      background: #0d0f14;
      border: 1px solid rgba(255,255,255,0.15);
      border-radius: 8px;
      padding: 12px 18px;
      font-size: 14px;
      color: #fff;
      outline: none;
    }
    .search-input:focus {
      border-color: #00f2fe;
    }

    /* Track Detail Card */
    .track-card {
      background: #12141a;
      border: 1px solid rgba(0, 242, 254, 0.35);
      box-shadow: 0 10px 30px rgba(0,0,0,0.5), 0 0 20px rgba(0, 242, 254, 0.15);
      border-radius: 14px;
      padding: 30px;
      margin-bottom: 40px;
    }

    .order-status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .badge-paid { background: rgba(0,242,254,0.15); color: #00f2fe; border: 1px solid #00f2fe; }
    .badge-assembling { background: rgba(251,191,36,0.15); color: #fbbf24; border: 1px solid #fbbf24; }
    .badge-testing { background: rgba(168,85,247,0.15); color: #c084fc; border: 1px solid #c084fc; }
    .badge-shipped { background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid #10b981; }
    .badge-completed { background: rgba(16,185,129,0.25); color: #34d399; border: 1px solid #34d399; }
    .badge-cancelled { background: rgba(255,51,75,0.15); color: #ff334b; border: 1px solid #ff334b; }

    /* Timeline Stepper */
    .timeline-stepper {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 10px;
      margin: 35px 0 30px;
      position: relative;
    }
    @media (max-width: 860px) {
      .timeline-stepper {
        grid-template-columns: 1fr;
        gap: 16px;
      }
    }
    .step-item {
      text-align: center;
      position: relative;
    }
    .step-circle {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: #161922;
      border: 2px solid rgba(255,255,255,0.15);
      color: #8c93a0;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 10px;
      font-size: 18px;
      transition: all 0.2s;
    }
    .step-item.active .step-circle {
      background: rgba(0,242,254,0.15);
      border-color: #00f2fe;
      color: #00f2fe;
      box-shadow: 0 0 15px rgba(0,242,254,0.4);
    }
    .step-item.done .step-circle {
      background: #10b981;
      border-color: #10b981;
      color: #000;
    }
    .step-title {
      font-size: 13px;
      font-weight: 700;
      color: #cbd5e1;
      margin-bottom: 3px;
    }
    .step-desc {
      font-size: 11px;
      color: #8c93a0;
    }

    /* Orders Table */
    .orders-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }
    .orders-table th {
      text-align: left;
      padding: 12px 16px;
      background: #161922;
      color: #8c93a0;
      font-size: 12px;
      text-transform: uppercase;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .orders-table td {
      padding: 14px 16px;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      color: #e2e8f0;
    }
    .orders-table tr:hover td {
      background: rgba(255,255,255,0.02);
    }
  </style>
</head>
<body>

  <?php require __DIR__ . '/layout/navbar.php'; ?>

  <div class="container">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
      <div>
        <div style="font-size: 12px; color: #00f2fe; font-weight: 700; text-transform: uppercase;">Real-Time Build & Courier Tracker (CRUD)</div>
        <h1 style="font-size: 28px; font-weight: 800; color: #fff; margin-top: 4px;">📦 订单装机流水与物流实时追踪</h1>
        <p style="font-size: 13px; color: #8c93a0;">全程可视透明化：付款到账 &rarr; 序列号分拣 &rarr; 工程师定制走线 &rarr; 双烤稳定性报告 &rarr; 专车发货。</p>
      </div>
      <div>
        <button onclick="openCreateOrderModal()" class="btn" style="background: #ff334b; color: #fff; padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 13px; border: none; cursor: pointer; box-shadow: 0 4px 15px rgba(255,51,75,0.4);">
          ➕ 手动登记新订单 (Create Order)
        </button>
      </div>
    </div>

    <!-- Search Form -->
    <form class="search-box" action="index.php" method="GET">
      <input type="hidden" name="page" value="tracking">
      <input type="text" name="order_no" class="search-input" value="<?= htmlspecialchars($orderNo) ?>" placeholder="输入订单编号 (例如 ORD-20261101-8891) 或收件人手机号...">
      <button type="submit" class="btn btn-primary" style="background: #00f2fe; color: #000; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 800; cursor: pointer;">
        🔍 立即追踪订单
      </button>
    </form>

    <!-- Tracked Order Result Details -->
    <?php if ($trackedOrder): ?>
      <?php
        $status = $trackedOrder['OrderStatus'] ?? 'Paid';
        $statusStages = [
          'Paid' => 1,
          'Assembling' => 3,
          'Testing' => 4,
          'Shipped' => 5,
          'Completed' => 6,
          'Cancelled' => 0
        ];
        $currentStage = $statusStages[$status] ?? 1;
      ?>
      <div class="track-card">
        
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
          <div>
            <div style="font-size: 12px; color: #8c93a0;">正在追踪订单详情：</div>
            <div style="font-size: 24px; font-weight: 900; color: #00f2fe;"><?= htmlspecialchars($trackedOrder['OrderNumber']) ?></div>
            <div style="font-size: 13px; color: #cbd5e1; margin-top: 4px;">
              👤 <strong>客户：</strong> <?= htmlspecialchars($trackedOrder['CustomerName'] ?? 'Gamer') ?> &bull; 
              📞 <strong>电话：</strong> <?= htmlspecialchars($trackedOrder['CustomerPhone'] ?? '') ?> &bull; 
              ✉️ <?= htmlspecialchars($trackedOrder['CustomerEmail'] ?? '') ?>
            </div>
            <div style="font-size: 12px; color: #8c93a0; margin-top: 4px;">
              📍 <strong>收件地址：</strong> <?= htmlspecialchars($trackedOrder['ShippingAddress'] ?? '吉隆坡展厅自提') ?>
            </div>
          </div>

          <div style="text-align: right;">
            <div style="margin-bottom: 8px;">
              <span class="order-status-badge badge-<?= strtolower($status) ?>">
                ● <?= htmlspecialchars($status) ?>
              </span>
            </div>
            <div style="font-size: 22px; font-weight: 800; color: #fff;">
              RM <?= number_format($trackedOrder['TotalSellingAmount'], 2) ?>
            </div>
            <div style="font-size: 11px; color: #8c93a0;">
              支付方式：<?= htmlspecialchars($trackedOrder['PaymentMethod'] ?? 'FPX Online Banking') ?>
            </div>
          </div>
        </div>

        <!-- Visual Timeline Stepper -->
        <div class="timeline-stepper">
          
          <div class="step-item <?= $currentStage >= 1 ? 'done' : '' ?>">
            <div class="step-circle">💳</div>
            <div class="step-title">1. 付款确认</div>
            <div class="step-desc">款项已入账流水</div>
          </div>

          <div class="step-item <?= $currentStage >= 2 ? ($currentStage > 2 ? 'done' : 'active') : ($currentStage >= 1 ? 'active' : '') ?>">
            <div class="step-circle">📦</div>
            <div class="step-title">2. 配件分配</div>
            <div class="step-desc">SN序列号出库核验</div>
          </div>

          <div class="step-item <?= $currentStage >= 3 ? ($currentStage > 3 ? 'done' : 'active') : '' ?>">
            <div class="step-circle">🔧</div>
            <div class="step-title">3. 定制装机</div>
            <div class="step-desc">专业工程师精细走线</div>
          </div>

          <div class="step-item <?= $currentStage >= 4 ? ($currentStage > 4 ? 'done' : 'active') : '' ?>">
            <div class="step-circle">🧪</div>
            <div class="step-title">4. 极限烤机</div>
            <div class="step-desc">3DMark 稳定性通过</div>
          </div>

          <div class="step-item <?= $currentStage >= 5 ? ($currentStage > 5 ? 'done' : 'active') : '' ?>">
            <div class="step-circle">🚚</div>
            <div class="step-title">5. 物流发货</div>
            <div class="step-desc"><?= htmlspecialchars($trackedOrder['Carrier'] ?? 'J&T') ?> 快运中</div>
          </div>

          <div class="step-item <?= $currentStage >= 6 ? 'done' : '' ?>">
            <div class="step-circle">✅</div>
            <div class="step-title">6. 签收送达</div>
            <div class="step-desc">三年原厂质保生效</div>
          </div>

        </div>

        <!-- Tracking Carrier & Number Info -->
        <div style="background: #0d0f14; border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 16px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
          <div>
            <div style="font-size: 11px; color: #8c93a0;">承运物流公司与运单号：</div>
            <div style="font-size: 15px; font-weight: 800; color: #fff;">
              <?= htmlspecialchars($trackedOrder['Carrier'] ?? 'J&T Express Cargo') ?>: 
              <span style="color: #fbbf24;"><?= htmlspecialchars($trackedOrder['TrackingNumber'] ?? '正在分拣打包') ?></span>
            </div>
          </div>
          <div style="display: flex; gap: 10px;">
            <!-- Edit Order Modal (CRUD - Update) -->
            <button onclick="openEditOrderModal(<?= htmlspecialchars(json_encode($trackedOrder)) ?>)" class="btn" style="background: #161922; color: #00f2fe; border: 1px solid rgba(0,242,254,0.3); padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer;">
              ✏️ 修改收件信息与装机进度 (Update)
            </button>
            <!-- Cancel / Delete Order (CRUD - Delete) -->
            <button onclick="deleteOrder(<?= $trackedOrder['OrderID'] ?>)" class="btn" style="background: #161922; color: #ff334b; border: 1px solid rgba(255,51,75,0.3); padding: 8px 14px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer;">
              🗑️ 取消订单 (Delete)
            </button>
          </div>
        </div>

        <!-- Items list if any -->
        <?php if (!empty($trackedOrder['items'])): ?>
          <h4 style="font-size: 14px; font-weight: 800; color: #fff; margin-bottom: 12px;">装机硬件配件清单 (Hardware Allocated):</h4>
          <div style="background: #0d0f14; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
              <?php foreach ($trackedOrder['items'] as $item): ?>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                  <td style="padding: 10px 16px; color: #fff; font-weight: 600;"><?= htmlspecialchars($item['name'] ?? 'Core Component') ?></td>
                  <td style="padding: 10px 16px; color: #8c93a0;"><?= htmlspecialchars($item['specs'] ?? '') ?></td>
                  <td style="padding: 10px 16px; text-align: right; color: #00f2fe; font-weight: 700;">RM <?= number_format($item['UnitPrice'] ?? 0, 2) ?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          </div>
        <?php endif; ?>

      </div>
    <?php elseif ($searched): ?>
      <div style="background: #12141a; border: 1px dashed rgba(255,51,75,0.4); border-radius: 12px; padding: 40px; text-align: center; margin-bottom: 40px;">
        <div style="font-size: 40px; margin-bottom: 10px;">🔍</div>
        <h3 style="color: #ff334b; font-size: 18px; font-weight: 800;">未查询到编号为 "<?= htmlspecialchars($orderNo) ?>" 的订单</h3>
        <p style="color: #8c93a0; font-size: 13px; margin-top: 6px;">请核对您支付凭据上的订单号或联系 WhatsApp 客服帮您人工核实。</p>
      </div>
    <?php endif; ?>

    <!-- All Orders Management Table (CRUD - Read) -->
    <div style="background: #12141a; border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 24px;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
        <h3 style="font-size: 17px; font-weight: 800; color: #fff;">📋 最近装机订单实时流水列表 (Recent Orders)</h3>
        <span style="font-size: 12px; color: #8c93a0;">共 <?= count($allOrders) ?> 笔有效订单记录</span>
      </div>

      <div style="overflow-x: auto;">
        <table class="orders-table">
          <thead>
            <tr>
              <th>订单编号 (Order No)</th>
              <th>客户姓名</th>
              <th>联系电话</th>
              <th>当前进度状态</th>
              <th>物流运单号</th>
              <th>订单总额</th>
              <th>下单时间</th>
              <th style="text-align: right;">操作 (CRUD)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allOrders as $o): ?>
              <tr>
                <td>
                  <a href="index.php?page=tracking&order_no=<?= urlencode($o['OrderNumber']) ?>" style="color: #00f2fe; font-weight: 700; text-decoration: none;">
                    <?= htmlspecialchars($o['OrderNumber']) ?>
                  </a>
                </td>
                <td><?= htmlspecialchars($o['CustomerName'] ?? 'Gamer') ?></td>
                <td><?= htmlspecialchars($o['CustomerPhone'] ?? '') ?></td>
                <td>
                  <span class="order-status-badge badge-<?= strtolower($o['OrderStatus'] ?? 'paid') ?>" style="padding: 3px 8px; font-size: 11px;">
                    <?= htmlspecialchars($o['OrderStatus'] ?? 'Paid') ?>
                  </span>
                </td>
                <td>
                  <span style="color: #fbbf24; font-size: 12px;"><?= htmlspecialchars($o['TrackingNumber'] ?? '-') ?></span>
                </td>
                <td style="font-weight: 700; color: #fff;">RM <?= number_format($o['TotalSellingAmount'], 2) ?></td>
                <td style="color: #5a6270; font-size: 12px;"><?= substr($o['CreatedAt'], 0, 10) ?></td>
                <td style="text-align: right;">
                  <button onclick="openEditOrderModal(<?= htmlspecialchars(json_encode($o)) ?>)" class="btn" style="background: none; border: none; color: #00f2fe; cursor: pointer; font-size: 12px;">
                    ✏️ 编辑
                  </button>
                  <button onclick="deleteOrder(<?= $o['OrderID'] ?>)" class="btn" style="background: none; border: none; color: #ff334b; cursor: pointer; font-size: 12px; margin-left: 6px;">
                    🗑️ 删除
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- Create Order Modal -->
  <div class="gopc-modal-overlay" id="createOrderModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #12141a; border: 1px solid rgba(255,255,255,0.15); border-radius: 14px; width: 100%; max-width: 500px; padding: 26px;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
        <h3 style="font-size: 17px; font-weight: 800; color: #fff;">➕ 手动登记新装机订单 (CRUD Create)</h3>
        <button onclick="closeCreateOrderModal()" style="background: none; border: none; color: #8c93a0; font-size: 18px; cursor: pointer;">&times;</button>
      </div>

      <form id="createOrderForm" onsubmit="submitCreateOrder(event)">
        <div style="margin-bottom: 14px;">
          <label style="display: block; font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 6px;">客户姓名 *</label>
          <input type="text" id="newCustName" class="form-input" style="width: 100%; background: #0d0f14; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 14px; border-radius: 8px;" placeholder="例如：Marcus Tan" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
          <div>
            <label style="display: block; font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 6px;">联系电话 *</label>
            <input type="text" id="newCustPhone" class="form-input" style="width: 100%; background: #0d0f14; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 14px; border-radius: 8px;" placeholder="+60 12-..." required>
          </div>
          <div>
            <label style="display: block; font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 6px;">电子邮箱</label>
            <input type="email" id="newCustEmail" class="form-input" style="width: 100%; background: #0d0f14; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 14px; border-radius: 8px;" placeholder="marcus@gmail.com">
          </div>
        </div>

        <div style="margin-bottom: 14px;">
          <label style="display: block; font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 6px;">收件地址</label>
          <textarea id="newShippingAddr" class="form-input" rows="2" style="width: 100%; background: #0d0f14; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 14px; border-radius: 8px;" placeholder="吉隆坡展厅自提 / 配送地址"></textarea>
        </div>

        <div style="margin-bottom: 20px;">
          <label style="display: block; font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 6px;">订单金额 (MYR)</label>
          <input type="number" step="0.01" id="newOrderAmount" class="form-input" style="width: 100%; background: #0d0f14; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 14px; border-radius: 8px;" value="5899.00" required>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
          <button type="button" onclick="closeCreateOrderModal()" style="background: #161922; color: #8c93a0; border: 1px solid rgba(255,255,255,0.1); padding: 9px 18px; border-radius: 8px; cursor: pointer;">取消</button>
          <button type="submit" id="btnSubmitNewOrder" style="background: #ff334b; color: #fff; border: none; padding: 9px 22px; border-radius: 8px; font-weight: 700; cursor: pointer;">创建订单</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Order Details & Status Modal -->
  <div class="gopc-modal-overlay" id="editOrderModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #12141a; border: 1px solid rgba(255,255,255,0.15); border-radius: 14px; width: 100%; max-width: 500px; padding: 26px;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
        <h3 style="font-size: 17px; font-weight: 800; color: #fff;" id="editModalTitle">✏️ 修改订单信息与装机状态 (CRUD Update)</h3>
        <button onclick="closeEditOrderModal()" style="background: none; border: none; color: #8c93a0; font-size: 18px; cursor: pointer;">&times;</button>
      </div>

      <form id="editOrderForm" onsubmit="submitEditOrder(event)">
        <input type="hidden" id="editOrderId" value="">

        <div style="margin-bottom: 14px;">
          <label style="display: block; font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 6px;">装机进度状态 (Order Status)</label>
          <select id="editStatus" class="form-input" style="width: 100%; background: #0d0f14; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 14px; border-radius: 8px;">
            <option value="Paid">💳 已付款确认 (Paid)</option>
            <option value="Assembling">🔧 工程师正在定制走线组装 (Assembling)</option>
            <option value="Testing">🧪 烤机与稳定性压力测试中 (Testing)</option>
            <option value="Shipped">🚚 已发出专车/物流转运 (Shipped)</option>
            <option value="Completed">✅ 客户已签收送达 (Completed)</option>
            <option value="Cancelled">❌ 订单已取消 (Cancelled)</option>
          </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
          <div>
            <label style="display: block; font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 6px;">物流公司 (Carrier)</label>
            <input type="text" id="editCarrier" class="form-input" style="width: 100%; background: #0d0f14; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 14px; border-radius: 8px;">
          </div>
          <div>
            <label style="display: block; font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 6px;">物流单号 (Tracking No)</label>
            <input type="text" id="editTrackingNo" class="form-input" style="width: 100%; background: #0d0f14; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 14px; border-radius: 8px;">
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
          <div>
            <label style="display: block; font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 6px;">客户姓名</label>
            <input type="text" id="editCustName" class="form-input" style="width: 100%; background: #0d0f14; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 14px; border-radius: 8px;">
          </div>
          <div>
            <label style="display: block; font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 6px;">联系电话</label>
            <input type="text" id="editCustPhone" class="form-input" style="width: 100%; background: #0d0f14; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 14px; border-radius: 8px;">
          </div>
        </div>

        <div style="margin-bottom: 20px;">
          <label style="display: block; font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 6px;">收件地址</label>
          <textarea id="editShippingAddr" class="form-input" rows="2" style="width: 100%; background: #0d0f14; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 14px; border-radius: 8px;"></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
          <button type="button" onclick="closeEditOrderModal()" style="background: #161922; color: #8c93a0; border: 1px solid rgba(255,255,255,0.1); padding: 9px 18px; border-radius: 8px; cursor: pointer;">取消</button>
          <button type="submit" id="btnSubmitEditOrder" style="background: #00f2fe; color: #000; border: none; padding: 9px 22px; border-radius: 8px; font-weight: 800; cursor: pointer;">保存更新</button>
        </div>
      </form>
    </div>
  </div>

  <?php require __DIR__ . '/layout/footer.php'; ?>
  <?php require __DIR__ . '/layout/ai_chat_widget.php'; ?>

  <script>
    function openCreateOrderModal() {
      document.getElementById('createOrderModal').style.display = 'flex';
    }
    function closeCreateOrderModal() {
      document.getElementById('createOrderModal').style.display = 'none';
    }

    async function submitCreateOrder(e) {
      e.preventDefault();
      const btn = document.getElementById('btnSubmitNewOrder');
      btn.disabled = true;

      const formData = new FormData();
      formData.append('customer_name', document.getElementById('newCustName').value.trim());
      formData.append('customer_phone', document.getElementById('newCustPhone').value.trim());
      formData.append('customer_email', document.getElementById('newCustEmail').value.trim());
      formData.append('shipping_address', document.getElementById('newShippingAddr').value.trim());
      formData.append('total_amount', document.getElementById('newOrderAmount').value);

      try {
        const res = await fetch('index.php?page=api-create-order', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
          closeCreateOrderModal();
          window.location.href = 'index.php?page=tracking&order_no=' + encodeURIComponent(data.order_number);
        } else {
          alert(data.message || '创建订单失败');
        }
      } catch(e) {
        alert('通信错误');
      } finally {
        btn.disabled = false;
      }
    }

    function openEditOrderModal(order) {
      document.getElementById('editOrderId').value = order.OrderID;
      document.getElementById('editCustName').value = order.CustomerName || '';
      document.getElementById('editCustPhone').value = order.CustomerPhone || '';
      document.getElementById('editCarrier').value = order.Carrier || '';
      document.getElementById('editTrackingNo').value = order.TrackingNumber || '';
      document.getElementById('editShippingAddr').value = order.ShippingAddress || '';
      document.getElementById('editStatus').value = order.OrderStatus || 'Paid';
      document.getElementById('editOrderModal').style.display = 'flex';
    }

    function closeEditOrderModal() {
      document.getElementById('editOrderModal').style.display = 'none';
    }

    async function submitEditOrder(e) {
      e.preventDefault();
      const btn = document.getElementById('btnSubmitEditOrder');
      btn.disabled = true;

      const formData = new FormData();
      formData.append('order_id', document.getElementById('editOrderId').value);
      formData.append('customer_name', document.getElementById('editCustName').value.trim());
      formData.append('customer_phone', document.getElementById('editCustPhone').value.trim());
      formData.append('carrier', document.getElementById('editCarrier').value.trim());
      formData.append('tracking_number', document.getElementById('editTrackingNo').value.trim());
      formData.append('shipping_address', document.getElementById('editShippingAddr').value.trim());
      formData.append('order_status', document.getElementById('editStatus').value);

      try {
        const res = await fetch('index.php?page=api-update-order', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
          closeEditOrderModal();
          window.location.reload();
        } else {
          alert(data.message || '更新失败');
        }
      } catch(e) {
        alert('通信异常');
      } finally {
        btn.disabled = false;
      }
    }

    async function deleteOrder(id) {
      if (!confirm('确定彻底取消并删除此订单记录吗？')) return;
      try {
        const formData = new FormData();
        formData.append('order_id', id);
        await fetch('index.php?page=api-delete-order', {
          method: 'POST',
          headers: { 'Accept': 'application/json' },
          body: formData
        });
        window.location.href = 'index.php?page=tracking';
      } catch(e) {
        alert('删除失败');
      }
    }
  </script>
</body>
</html>
