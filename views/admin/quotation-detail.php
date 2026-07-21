<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div style="display:flex;align-items:center;gap:12px">
    <a href="/admin/quotations" class="btn btn-ghost" style="padding:6px 12px">← Danh sách</a>
    <div>
      <h1>Chi tiết báo giá độc lập #<?= e($quotation['code']) ?></h1>
      <p style="color:#667085;margin:0">Quản lý trạng thái báo giá, in ấn và chuyển đổi thành đơn hàng bán.</p>
    </div>
  </div>
  <div style="display:flex;gap:10px">
    <?php if($quotation['status'] === 'pending'): ?>
    <form method="post" action="/admin/quotations/<?= (int)$quotation['id'] ?>/status" style="margin:0">
      <?= csrfField() ?><input type="hidden" name="status" value="sent">
      <button class="btn btn-outline" type="submit">Gửi cho khách hàng</button>
    </form>
    <?php endif; ?>

    <?php if(in_array($quotation['status'], ['pending','sent'], true)): ?>
    <form method="post" action="/admin/quotations/<?= (int)$quotation['id'] ?>/convert" style="margin:0" onsubmit="return csConfirmForm(this, 'Xác nhận tạo Đơn hàng chính thức từ báo giá này? Số lượng tồn kho sản phẩm sẽ được kiểm tra.')">
      <?= csrfField() ?>
      <button class="btn btn-navy" type="submit">⚡ Chuyển thành Đơn hàng</button>
    </form>
    <form method="post" action="/admin/quotations/<?= (int)$quotation['id'] ?>/status" style="margin:0">
      <?= csrfField() ?><input type="hidden" name="status" value="cancelled">
      <button class="btn" style="background:#ef4444;color:#fff;border:none;border-radius:4px" type="submit">Hủy báo giá</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.detail-grid{display:grid;grid-template-columns:1fr 300px;gap:20px;margin-bottom:20px}
.detail-card{background:#fff;border:1px solid #e6ebf1;border-radius:8px;padding:20px}
.item-table{width:100%;border-collapse:collapse;margin-top:14px}
.item-table th{background:#f8fafc;padding:10px 8px;font-size:11px;text-transform:uppercase;color:#64748b;text-align:left;border-bottom:1px solid #e6ebf1}
.item-table td{padding:10px 8px;border-top:1px solid #edf1f5;font-size:13px}
.q-status{padding:3px 8px;border-radius:4px;font-size:12px;font-weight:700}
.q-status-pending{background:#fef9c3;color:#854d0e;border:1px solid #fde68a}
.q-status-sent{background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe}
.q-status-converted{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}
.q-status-expired{background:#f3f4f6;color:#374151;border:1px solid #d1d5db}
.q-status-cancelled{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca}
</style>

<div class="detail-grid">
  <!-- Cột Chính: Chi tiết sản phẩm báo giá -->
  <div class="detail-card">
    <h3 style="margin:0 0 14px;color:#1a3258">Danh sách sản phẩm được báo giá</h3>
    <table class="item-table">
      <thead>
        <tr>
          <th>Sản phẩm</th>
          <th>Số lượng</th>
          <th style="text-align:right">Đơn giá bán</th>
          <th style="text-align:right">Thành tiền</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($items as $item): ?>
        <tr>
          <td>
            <strong><?= e($item['product_name']) ?></strong>
            <div style="font-size:11px;color:#6b7280;margin-top:2px">SKU: <?= e($item['sku']) ?></div>
          </td>
          <td><?= number_format((int)$item['quantity']) ?></td>
          <td style="text-align:right"><?= number_format((int)$item['price']) ?> đ</td>
          <td style="text-align:right;font-weight:700;color:#1e3a8a"><?= number_format((int)$item['total']) ?> đ</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div style="display:flex;justify-content:flex-end;margin-top:20px;padding-top:16px;border-top:1px solid #edf2f7">
      <div style="font-size:14px;text-align:right">
        <span style="color:#718096">Tổng giá trị báo giá:</span>
        <div style="font-size:22px;font-weight:800;color:#1a3258;margin-top:4px"><?= number_format((int)$quotation['grand_total']) ?> đ</div>
      </div>
    </div>
  </div>

  <!-- Cột Phải: Thông tin khách hàng & Báo giá -->
  <div>
    <div class="detail-card" style="margin-bottom:16px">
      <h3 style="margin:0 0 12px;color:#1a3258;font-size:14px">Thông tin chung</h3>
      <div style="font-size:13px;line-height:1.6">
        <div style="margin-bottom:10px">
          <span style="color:#718096;display:block">Trạng thái:</span>
          <span class="q-status q-status-<?= e($quotation['status']) ?>">
            <?= ['pending'=>'Chờ duyệt','sent'=>'Đã gửi KH','converted'=>'Đã xuất đơn','expired'=>'Hết hạn','cancelled'=>'Đã hủy'][$quotation['status']] ?? e($quotation['status']) ?>
          </span>
        </div>
        <div style="margin-bottom:10px">
          <span style="color:#718096;display:block">Khách hàng:</span>
          <strong><?= e($quotation['customer_name']) ?></strong>
          <div>SĐT: <?= e($quotation['customer_phone'] ?: '—') ?></div>
          <a href="/admin/users/<?= (int)$quotation['user_id'] ?>" style="font-size:11px;color:#1a3258;text-decoration:none;font-weight:700">Xem hồ sơ chi tiết →</a>
        </div>
        <div style="margin-bottom:10px">
          <span style="color:#718096;display:block">Ngày hết hạn:</span>
          <strong style="color:#e11d48"><?= e(substr($quotation['expires_at'],0,10)) ?></strong>
        </div>
        <div style="margin-bottom:10px">
          <span style="color:#718096;display:block">Ngày lập:</span>
          <strong><?= date('d/m/Y H:i', strtotime($quotation['created_at'])) ?></strong>
        </div>
        <div>
          <span style="color:#718096;display:block">Người lập:</span>
          <strong><?= e($quotation['creator_name'] ?: 'System') ?></strong>
        </div>
      </div>
    </div>

    <?php if($quotation['note']): ?>
    <div class="detail-card">
      <h3 style="margin:0 0 8px;color:#1a3258;font-size:14px">Ghi chú</h3>
      <p style="font-size:13px;color:#4a5568;margin:0;line-height:1.5"><?= nl2br(e($quotation['note'])) ?></p>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
