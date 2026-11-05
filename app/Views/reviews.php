<?php
/**
 * @var string $pageTitle
 * @var array $reviews
 * @var array $stats
 * @var string $filter
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
    
    /* Overall Rating Card */
    .rating-banner {
      background: #12141a;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 14px;
      padding: 32px;
      display: grid;
      grid-template-columns: 280px 1fr 220px;
      gap: 30px;
      align-items: center;
      margin-bottom: 35px;
    }
    @media (max-width: 850px) {
      .rating-banner {
        grid-template-columns: 1fr;
      }
    }

    .rating-score {
      font-size: 56px;
      font-weight: 900;
      color: #fff;
      line-height: 1;
      margin-bottom: 6px;
    }
    .rating-stars {
      color: #fbbf24;
      font-size: 20px;
      margin-bottom: 6px;
    }

    /* Distribution Bars */
    .dist-row {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 12px;
      color: #8c93a0;
      margin-bottom: 6px;
    }
    .dist-bar {
      flex: 1;
      height: 8px;
      background: rgba(255,255,255,0.08);
      border-radius: 4px;
      overflow: hidden;
    }
    .dist-fill {
      height: 100%;
      background: #fbbf24;
      border-radius: 4px;
    }

    /* Filter tabs */
    .filter-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
      flex-wrap: wrap;
      gap: 14px;
    }
    .filter-pills {
      display: flex;
      gap: 8px;
    }
    .filter-btn {
      background: #12141a;
      border: 1px solid rgba(255,255,255,0.08);
      color: #8c93a0;
      padding: 7px 16px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.15s;
    }
    .filter-btn:hover,
    .filter-btn.active {
      border-color: #ff334b;
      color: #fff;
      background: rgba(255,51,75,0.1);
    }

    /* Reviews Grid */
    .reviews-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
      gap: 20px;
    }
    .review-card {
      background: #12141a;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 12px;
      padding: 22px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: all 0.2s;
    }
    .review-card:hover {
      border-color: rgba(255,255,255,0.18);
      box-shadow: 0 6px 20px rgba(0,0,0,0.4);
    }
    .rev-user {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 12px;
    }
    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, #ff334b 0%, #00f2fe 100%);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 16px;
    }
    .user-name {
      font-size: 14px;
      font-weight: 700;
      color: #fff;
    }
    .verified-badge {
      font-size: 10px;
      font-weight: 700;
      color: #10b981;
      background: rgba(16,185,129,0.12);
      border: 1px solid rgba(16,185,129,0.3);
      padding: 2px 6px;
      border-radius: 4px;
      margin-left: 6px;
    }
    .rev-specs {
      font-size: 11px;
      color: #00f2fe;
      background: rgba(0,242,254,0.08);
      padding: 4px 8px;
      border-radius: 4px;
      display: inline-block;
      margin-bottom: 10px;
    }
    .rev-text {
      font-size: 13px;
      color: #cbd5e1;
      line-height: 1.6;
      margin-bottom: 16px;
      flex: 1;
    }
    .rev-footer {
      border-top: 1px solid rgba(255,255,255,0.06);
      padding-top: 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 12px;
      color: #5a6270;
    }
    .rev-actions {
      display: flex;
      gap: 10px;
      align-items: center;
    }
    .rev-btn-action {
      background: none;
      border: none;
      color: #8c93a0;
      cursor: pointer;
      font-size: 12px;
      transition: color 0.15s;
    }
    .rev-btn-action:hover {
      color: #fff;
    }

    /* Interactive Stars in Modal */
    .star-select {
      display: flex;
      gap: 6px;
      font-size: 26px;
      cursor: pointer;
      color: #475569;
    }
    .star-select span.active {
      color: #fbbf24;
    }
  </style>
</head>
<body>

  <?php require __DIR__ . '/layout/navbar.php'; ?>

  <div class="container">

    <div style="margin-bottom: 24px;">
      <div style="font-size: 12px; color: #ff334b; font-weight: 700; text-transform: uppercase;">Google Verified Reviews (CRUD)</div>
      <h1 style="font-size: 28px; font-weight: 800; color: #fff; margin-top: 4px;">Google 真实装机好评与客户口碑</h1>
      <p style="font-size: 13px; color: #8c93a0;">来自全马各地电竞玩家、内容创作者与企业机构的真实定制装机反馈体验。</p>
    </div>

    <!-- Rating Summary Banner -->
    <div class="rating-banner">
      <!-- Col 1: Big Score -->
      <div>
        <div style="font-size: 12px; color: #8c93a0; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">综合客户满意度</div>
        <div class="rating-score"><?= $stats['avg_rating'] ?></div>
        <div class="rating-stars">★★★★★</div>
        <div style="font-size: 12px; color: #10b981; font-weight: 700;">🟢 GOOGLE VERIFIED MERCHANT</div>
        <div style="font-size: 11px; color: #8c93a0; margin-top: 2px;">基于 <?= $stats['total'] ?> 条已验证的装机晒单</div>
      </div>

      <!-- Col 2: Star Breakdown -->
      <div>
        <div class="dist-row">
          <span>5 星 (5★)</span>
          <div class="dist-bar">
            <div class="dist-fill" style="width: <?= $stats['total'] > 0 ? round(($stats['stars_5'] / $stats['total']) * 100) : 100 ?>%;"></div>
          </div>
          <span><?= $stats['stars_5'] ?></span>
        </div>
        <div class="dist-row">
          <span>4 星 (4★)</span>
          <div class="dist-bar">
            <div class="dist-fill" style="width: <?= $stats['total'] > 0 ? round(($stats['stars_4'] / $stats['total']) * 100) : 0 ?>%;"></div>
          </div>
          <span><?= $stats['stars_4'] ?></span>
        </div>
        <div class="dist-row">
          <span>3 星 (3★)</span>
          <div class="dist-bar">
            <div class="dist-fill" style="width: 0%;"></div>
          </div>
          <span>0</span>
        </div>
        <div class="dist-row">
          <span>2 星 (2★)</span>
          <div class="dist-bar">
            <div class="dist-fill" style="width: 0%;"></div>
          </div>
          <span>0</span>
        </div>
        <div class="dist-row">
          <span>1 星 (1★)</span>
          <div class="dist-bar">
            <div class="dist-fill" style="width: 0%;"></div>
          </div>
          <span>0</span>
        </div>
      </div>

      <!-- Col 3: Write Review CTA -->
      <div style="text-align: center;">
        <div style="font-size: 12px; color: #8c93a0; margin-bottom: 12px;">装机体验满意？分享您的真实心得！</div>
        <button class="btn btn-primary" onclick="openReviewModal()" style="width: 100%; justify-content: center; padding: 12px; font-weight: 800; font-size: 14px; background: #ff334b; color: #fff; border: none; border-radius: 8px; cursor: pointer; box-shadow: 0 4px 15px rgba(255,51,75,0.4);">
          ✍️ 撰写 Google 评价
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
      <div class="filter-pills">
        <a href="index.php?page=reviews&filter=all" class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">全部评价 (<?= $stats['total'] ?>)</a>
        <a href="index.php?page=reviews&filter=5" class="filter-btn <?= $filter === '5' ? 'active' : '' ?>">⭐⭐⭐⭐⭐ 五星好评 (<?= $stats['stars_5'] ?>)</a>
        <a href="index.php?page=reviews&filter=4" class="filter-btn <?= $filter === '4' ? 'active' : '' ?>">⭐⭐⭐⭐ 四星好评 (<?= $stats['stars_4'] ?>)</a>
      </div>
      <div style="font-size: 12px; color: #8c93a0;">
        按最新提交时间排序
      </div>
    </div>

    <!-- Reviews Grid -->
    <div class="reviews-list">
      <?php foreach ($reviews as $rev): ?>
        <div class="review-card" id="revCard_<?= $rev['id'] ?>">
          <div>
            <div class="rev-user">
              <div class="user-avatar"><?= mb_substr($rev['author_name'], 0, 1) ?></div>
              <div>
                <div class="user-name">
                  <?= htmlspecialchars($rev['author_name']) ?>
                  <?php if (!empty($rev['is_verified'])): ?>
                    <span class="verified-badge">✓ 已验货买家</span>
                  <?php endif; ?>
                </div>
                <div style="color: #fbbf24; font-size: 12px; margin-top: 2px;">
                  <?= str_repeat('⭐', (int)$rev['rating']) ?>
                </div>
              </div>
            </div>

            <?php if (!empty($rev['specs_built'])): ?>
              <div class="rev-specs">
                💻 定制配置：<?= htmlspecialchars($rev['specs_built']) ?>
              </div>
            <?php endif; ?>

            <p class="rev-text">"<?= htmlspecialchars($rev['review_text']) ?>"</p>
          </div>

          <div class="rev-footer">
            <span><?= htmlspecialchars(substr($rev['created_at'], 0, 10)) ?></span>
            <div class="rev-actions">
              <!-- Like Button -->
              <button class="rev-btn-action" onclick="likeReview(<?= $rev['id'] ?>, this)">
                👍 有帮助 (<span class="like-cnt"><?= (int)$rev['likes_count'] ?></span>)
              </button>
              <!-- Edit Button (CRUD - Update) -->
              <button class="rev-btn-action" onclick="editReview(<?= htmlspecialchars(json_encode($rev)) ?>)" title="修改此评价">
                ✏️ 编辑
              </button>
              <!-- Delete Button (CRUD - Delete) -->
              <button class="rev-btn-action" onclick="deleteReview(<?= $rev['id'] ?>)" title="删除此评价">
                🗑️
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>

  <!-- Review Create / Edit Modal -->
  <div class="gopc-modal-overlay" id="reviewModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #12141a; border: 1px solid rgba(255,255,255,0.15); border-radius: 14px; width: 100%; max-width: 480px; padding: 26px;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
        <h3 style="font-size: 18px; font-weight: 800; color: #fff;" id="revModalTitle">✍️ 撰写 Google Review 评价</h3>
        <button onclick="closeReviewModal()" style="background: none; border: none; color: #8c93a0; font-size: 18px; cursor: pointer;">&times;</button>
      </div>

      <form id="reviewForm" onsubmit="submitReview(event)">
        <input type="hidden" id="revId" value="">

        <div style="margin-bottom: 16px;">
          <label style="display: block; font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 6px;">您的评分 (Rating)</label>
          <div class="star-select" id="starSelect">
            <span onclick="setRating(1)">★</span>
            <span onclick="setRating(2)">★</span>
            <span onclick="setRating(3)">★</span>
            <span onclick="setRating(4)">★</span>
            <span onclick="setRating(5)">★</span>
          </div>
          <input type="hidden" id="revRating" value="5">
        </div>

        <div style="margin-bottom: 14px;">
          <label style="display: block; font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 6px;">您的称呼 / Google 昵称 *</label>
          <input type="text" id="revAuthor" class="form-input" style="width: 100%; background: #0d0f14; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 14px; border-radius: 8px;" placeholder="例如：Kelvin Lee" required>
        </div>

        <div style="margin-bottom: 14px;">
          <label style="display: block; font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 6px;">装机配置型号 (可选)</label>
          <input type="text" id="revSpecs" class="form-input" style="width: 100%; background: #0d0f14; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 14px; border-radius: 8px;" placeholder="例如：AMD 7800X3D + RTX 4070Ti Super">
        </div>

        <div style="margin-bottom: 20px;">
          <label style="display: block; font-size: 12px; font-weight: 700; color: #8c93a0; margin-bottom: 6px;">详细评价反馈 *</label>
          <textarea id="revText" class="form-input" rows="4" style="width: 100%; background: #0d0f14; border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 10px 14px; border-radius: 8px;" placeholder="分享走线管理、游戏温度、客服沟通体验..." required></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
          <button type="button" onclick="closeReviewModal()" style="background: #161922; color: #8c93a0; border: 1px solid rgba(255,255,255,0.1); padding: 9px 18px; border-radius: 8px; cursor: pointer;">取消</button>
          <button type="submit" id="btnSubmitRev" style="background: #ff334b; color: #fff; border: none; padding: 9px 22px; border-radius: 8px; font-weight: 700; cursor: pointer;">提交评价</button>
        </div>
      </form>
    </div>
  </div>

  <?php require __DIR__ . '/layout/footer.php'; ?>
  <?php require __DIR__ . '/layout/ai_chat_widget.php'; ?>

  <script>
    function setRating(val) {
      document.getElementById('revRating').value = val;
      const stars = document.querySelectorAll('#starSelect span');
      stars.forEach((s, idx) => {
        s.classList.toggle('active', idx < val);
      });
    }

    function openReviewModal() {
      document.getElementById('revId').value = '';
      document.getElementById('revModalTitle').textContent = '✍️ 撰写 Google Review 评价';
      document.getElementById('revAuthor').value = '';
      document.getElementById('revSpecs').value = '';
      document.getElementById('revText').value = '';
      setRating(5);
      document.getElementById('reviewModal').style.display = 'flex';
    }

    function editReview(data) {
      document.getElementById('revId').value = data.id;
      document.getElementById('revModalTitle').textContent = '✏️ 修改我的 Google 评价 (ID #' + data.id + ')';
      document.getElementById('revAuthor').value = data.author_name;
      document.getElementById('revSpecs').value = data.specs_built || '';
      document.getElementById('revText').value = data.review_text;
      setRating(parseInt(data.rating) || 5);
      document.getElementById('reviewModal').style.display = 'flex';
    }

    function closeReviewModal() {
      document.getElementById('reviewModal').style.display = 'none';
    }

    async function submitReview(e) {
      e.preventDefault();
      const id = document.getElementById('revId').value;
      const author = document.getElementById('revAuthor').value.trim();
      const specs = document.getElementById('revSpecs').value.trim();
      const text = document.getElementById('revText').value.trim();
      const rating = document.getElementById('revRating').value;

      const btn = document.getElementById('btnSubmitRev');
      btn.disabled = true;

      const formData = new FormData();
      formData.append('id', id);
      formData.append('author_name', author);
      formData.append('specs_built', specs);
      formData.append('review_text', text);
      formData.append('rating', rating);

      const endpoint = id ? 'index.php?page=api-update-review' : 'index.php?page=api-create-review';

      try {
        const res = await fetch(endpoint, {
          method: 'POST',
          body: formData
        });
        const d = await res.json();
        if (d.success) {
          closeReviewModal();
          window.location.reload();
        } else {
          alert(d.message || '提交失败');
        }
      } catch(err) {
        alert('网络通信故障');
      } finally {
        btn.disabled = false;
      }
    }

    async function deleteReview(id) {
      if (!confirm('确定删除该评价记录吗？')) return;
      try {
        const formData = new FormData();
        formData.append('id', id);
        await fetch('index.php?page=api-delete-review', {
          method: 'POST',
          headers: { 'Accept': 'application/json' },
          body: formData
        });
        const card = document.getElementById('revCard_' + id);
        if (card) {
          card.style.opacity = '0.3';
          setTimeout(() => card.remove(), 250);
        }
      } catch(err) {
        alert('删除失败');
      }
    }

    async function likeReview(id, btn) {
      const formData = new FormData();
      formData.append('id', id);
      try {
        const res = await fetch('index.php?page=api-like-review', { method: 'POST', body: formData });
        const d = await res.json();
        if (d.success) {
          const span = btn.querySelector('.like-cnt');
          span.textContent = parseInt(span.textContent || 0) + 1;
          btn.style.color = '#00f2fe';
          btn.disabled = true;
        }
      } catch(e) {}
    }

    // Set initial stars
    document.addEventListener('DOMContentLoaded', () => setRating(5));
  </script>
</body>
</html>
