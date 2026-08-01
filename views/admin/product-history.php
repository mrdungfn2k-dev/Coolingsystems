<?php /** @var array $product @var array $history @var array $views @var int $viewTotal */ ?>
<?php include __DIR__ . '/../partials/dashboard-head.php'; ?>
<style>
.history-page { max-width: 1200px; margin: 0 auto; padding: 24px 16px; }
.history-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
.btn-back { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; background: #f1f5f9; color: #1e293b; font-size: .875rem; font-weight: 600; text-decoration: none; transition: .15s; }
.btn-back:hover { background: #e2e8f0; }

.tab-nav { display: flex; gap: 8px; border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; }
.tab-btn { padding: 10px 20px; font-weight: 700; font-size: 14px; color: #64748b; background: none; border: none; border-bottom: 3px solid transparent; cursor: pointer; margin-bottom: -2px; transition: all 0.2s; }
.tab-btn.active { color: #1a3258; border-bottom-color: #1a3258; }

.stat-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; display: inline-flex; align-items: center; gap: 14px; margin-bottom: 20px; }
.stat-card .val { font-size: 24px; font-weight: 800; color: #1a3258; }
.stat-card .lbl { font-size: 12.5px; color: #64748b; font-weight: 600; }

.history-table { width: 100%; border-collapse: collapse; font-size: .875rem; background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; }
.history-table th { background: #f1f5f9; color: #475569; font-weight: 700; padding: 12px 14px; text-align: left; border-bottom: 2px solid #e2e8f0; }
.history-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.history-table tr:hover td { background: #f8fafc; }

.action-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 99px; font-size: .78rem; font-weight: 600; }
.action-create    { background: #dcfce7; color: #166534; }
.action-update    { background: #dbeafe; color: #1e40af; }
.action-inventory { background: #fef9c3; color: #854d0e; }
.action-status    { background: #f3e8ff; color: #6b21a8; }

.restore-btn { padding: 5px 14px; border-radius: 6px; border: 1px solid #1a3258; background: #fff; color: #1a3258; font-size: .8rem; font-weight: 700; cursor: pointer; transition: .15s; }
.restore-btn:hover { background: #1a3258; color: #fff; }
</style>

<div class="history-page">
  <div class="history-header">
    <div>
      <a class="btn-back" href="/admin/products/<?= e($product['id']) ?>/edit">← Quay lại chỉnh sửa</a>
      <h2 style="margin:10px 0 4px;font-size:1.4rem;font-weight:700;color:#1e293b">Dữ liệu & Lịch sử: <?= e($product['name']) ?></h2>
      <div style="color:#64748b;font-size:13px">SKU: <strong><?= e($product['sku'] ?? '—') ?></strong> &nbsp;·&nbsp; OEM: <strong><?= e($product['oem_code'] ?? '—') ?></strong></div>
    </div>
  </div>

  <!-- Stat summary -->
  <div class="stat-card">
    <div style="width:42px;height:42px;border-radius:50%;background:#e0f2fe;color:#0369a1;display:flex;align-items:center;justify-content:center;font-size:20px">👁️</div>
    <div>
      <div class="val"><?= number_format((int)($viewTotal ?? count($views ?? []))) ?></div>
      <div class="lbl">Tổng lượt xem & truy cập sản phẩm</div>
    </div>
  </div>

  <div class="tab-nav">
    <button type="button" class="tab-btn active" onclick="switchHistTab('viewsTab', this)">👁️ Lượt truy cập (<?= count($views ?? []) ?>)</button>
    <button type="button" class="tab-btn" onclick="switchHistTab('changesTab', this)">📜 Lịch sử chỉnh sửa & Khôi phục (<?= count($history ?? []) ?>)</button>
  </div>

  <!-- TAB 1: LƯỢT TRUY CẬP -->
  <div id="viewsTab" class="hist-tab-content">
    <?php if (empty($views)): ?>
      <div style="text-align:center;padding:40px;background:#fff;border-radius:8px;border:1px solid #e2e8f0;color:#94a3b8;font-weight:600">
        Chưa ghi nhận lượt truy cập chi tiết cho sản phẩm này.
      </div>
    <?php else: ?>
      <table class="history-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Thời gian</th>
            <th>Người truy cập</th>
            <th>Email</th>
            <th>Chi tiết thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($views as $idx => $v): ?>
            <tr>
              <td><?= $idx + 1 ?></td>
              <td style="white-space:nowrap;font-size:12.5px;color:#475569"><?= date('d/m/Y H:i:s', strtotime($v['created_at'])) ?></td>
              <td style="font-weight:600;color:#1e293b"><?= e($v['full_name'] ?? 'Khách xem web') ?></td>
              <td style="font-size:12.5px;color:#64748b"><?= e($v['email'] ?? '—') ?></td>
              <td style="font-size:12.5px;color:#475569"><span class="action-badge action-update">Xem chi tiết SP</span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <!-- TAB 2: LỊCH SỬ CHỈNH SỬA & KHÔI PHỤC -->
  <div id="changesTab" class="hist-tab-content" style="display:none">
    <?php if (empty($history)): ?>
      <div style="text-align:center;padding:40px;background:#fff;border-radius:8px;border:1px solid #e2e8f0;color:#94a3b8;font-weight:600">
        Chưa có lịch sử thay đổi phiên bản nào cho sản phẩm này.
      </div>
    <?php else: ?>
      <table class="history-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Thời gian</th>
            <th>Loại thay đổi</th>
            <th>Người thực hiện</th>
            <th>Dữ liệu phiên bản đó</th>
            <th style="text-align:center">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($history as $idx => $h):
            $actionClass = 'action-' . ($h['action'] ?? 'update');
            $actionLabel = match($h['action'] ?? '') {
              'create'    => 'Tạo mới',
              'update'    => 'Cập nhật SP',
              'inventory' => 'Kho / Giá',
              'status'    => 'Trạng thái',
              default     => $h['action'] ?? 'update'
            };
            $fmtMoney = fn($val) => number_format((int)$val, 0, ',', '.') . 'đ';
          ?>
            <tr>
              <td><?= $idx + 1 ?></td>
              <td style="white-space:nowrap;font-size:12.5px;color:#475569"><?= e($h['changed_at']) ?></td>
              <td><span class="action-badge <?= $actionClass ?>"><?= $actionLabel ?></span></td>
              <td style="font-weight:600;color:#1e293b"><?= e($h['changer_name'] ?? 'Admin') ?></td>
              <td style="font-size:12.5px;color:#475569">
                <?php if ($h['action'] === 'create'): ?>
                  Tạo ban đầu — Giá: <?= $fmtMoney($h['price'] ?? 0) ?> · Kho: <?= (int)($h['stock'] ?? 0) ?> · Trạng thái: <?= e($h['status'] ?? '') ?>
                <?php elseif ($h['action'] === 'inventory'): ?>
                  Giá: <?= $fmtMoney($h['price'] ?? 0) ?> · Kho: <?= (int)($h['stock'] ?? 0) ?> · Giá gốc: <?= $fmtMoney($h['original_price'] ?? 0) ?>
                <?php else: ?>
                  <?= e(mb_substr($h['name'] ?? '', 0, 40)) ?> · Giá: <?= $fmtMoney($h['price'] ?? 0) ?> · Kho: <?= (int)($h['stock'] ?? 0) ?> · <?= e($h['status'] ?? '') ?>
                <?php endif; ?>
              </td>
              <td style="text-align:center">
                <?php if ($h['action'] !== 'create'): ?>
                  <form method="POST" action="/admin/products/<?= e($product['id']) ?>/restore/<?= $h['id'] ?>" onsubmit="return confirm('Khôi phục sản phẩm về bản này?\nThao tác này sẽ ghi đè nội dung hiện tại!')" style="margin:0">
                    <?= csrfField() ?>
                    <button type="submit" class="restore-btn">Khôi phục</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<script>
function switchHistTab(tabId, btn) {
  document.querySelectorAll('.hist-tab-content').forEach(function(el) { el.style.display = 'none'; });
  document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
  document.getElementById(tabId).style.display = 'block';
  btn.classList.add('active');
}
</script>

<?php include __DIR__ . '/../partials/dashboard-foot.php'; ?>
