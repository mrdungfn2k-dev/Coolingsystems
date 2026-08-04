<?php require __DIR__ . '/../partials/head.php'; ?>
<style>
.warranty-full-wrap {
  width: 100%;
  padding: 24px 40px 60px;
  box-sizing: border-box;
}
@media (max-width: 768px) {
  .warranty-full-wrap { padding: 16px 16px 40px; }
}
.warranty-header-box {
  background: var(--navy-dark);
  color: #ffffff;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  text-align: center;
}
.warranty-header-box h1 {
  font-size: 22px;
  font-weight: 800;
  color: #ffffff;
  margin: 0 0 6px;
}
.warranty-header-box p {
  font-size: 13px;
  opacity: 0.85;
  margin: 0;
}
.search-form-row {
  display: flex;
  gap: 12px;
  max-width: 700px;
  margin: 0 auto 24px;
}
@media (max-width: 500px) {
  .search-form-row { flex-direction: column; }
}
.search-form-row input {
  flex: 1;
  height: 44px;
  border-radius: 8px;
  border: 1px solid var(--line);
  padding: 0 14px;
  font-size: 14px;
  font-weight: 600;
  outline: none;
}
.search-form-row button {
  height: 44px;
  padding: 0 24px;
  background: var(--navy);
  color: #ffffff;
  border-radius: 8px;
  border: none;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  white-space: nowrap;
}
.search-form-row button:hover {
  background: var(--navy-dark);
}
.warranty-result-card {
  background: #ffffff;
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 16px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}
.status-badge-ok {
  background: #dcfce7;
  color: #15803d;
  font-weight: 800;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  display: inline-block;
  border: 1px solid #bbf7d0;
}
.status-badge-expired {
  background: #fef2f2;
  color: #b91c1c;
  font-weight: 800;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  display: inline-block;
  border: 1px solid #fca5a5;
}
</style>

<div class="warranty-full-wrap">
  
  <div class="warranty-header-box">
    <h1>TRA CỨU PHIẾU BẢO HÀNH ĐIỆN TỬ</h1>
    <p>Nhập Mã Serial, Số điện thoại hoặc Mã đơn hàng để tra cứu thời hạn bảo hành phụ tùng</p>
  </div>

  <form method="get" action="/warranty/lookup" class="search-form-row">
    <input type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" required maxlength="40" placeholder="Mã Serial, Số điện thoại hoặc Mã đơn hàng...">
    <button type="submit">TRA CỨU</button>
  </form>

  <?php if (isset($_GET['q'])): ?>
    <?php if (empty($cases)): ?>
      <div style="text-align:center; padding:40px 20px; color:var(--ink-2); font-size:14px; background:#fff; border-radius:12px; border:1px solid var(--line);">
        Không tìm thấy thông tin phiếu bảo hành khớp với từ khóa "<strong><?= e($_GET['q']) ?></strong>".<br>
        <small style="color:var(--ink-3); margin-top:6px; display:block;">Vui lòng kiểm tra lại Mã Serial trên vỏ hộp hoặc liên hệ Hotline Bảo hành: <strong>0704.0704.18</strong></small>
      </div>
    <?php else: ?>
      <div style="font-size:15px; font-weight:800; color:var(--navy-dark); margin-bottom:14px;">
        KẾT QUẢ TRA CỨU (<?= count($cases) ?> PHIẾU):
      </div>

      <?php foreach ($cases as $c): ?>
        <?php
          $isExpired = strtotime($c['warranty_end_date']) < time();
          $daysLeft = ceil((strtotime($c['warranty_end_date']) - time()) / 86400);
        ?>
        <div class="warranty-result-card">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
            <div>
              <span style="font-size:11px; font-weight:800; color:var(--ink-3);">MÃ PHIẾU: <?= e($c['case_code']) ?></span>
              <div style="font-size:16px; font-weight:800; color:var(--navy-dark); margin-top:2px;"><?= e($c['product_name'] ?? 'Linh kiện Phụ tùng Điều hòa Ô tô') ?></div>
            </div>
            <div>
              <?php if ($isExpired): ?>
                <span class="status-badge-expired">HẾT HẠN BẢO HÀNH</span>
              <?php else: ?>
                <span class="status-badge-ok">CÒN BẢO HÀNH (Còn <?= $daysLeft ?> ngày)</span>
              <?php endif; ?>
            </div>
          </div>

          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px; font-size:13px; color:var(--ink-1); border-top:1px solid #edf1f5; padding-top:12px;">
            <div><strong>Serial Number:</strong> <?= e($c['serial_no'] ?: 'Tem điện tử hệ thống') ?></div>
            <div><strong>Mã đơn hàng:</strong> <?= e($c['order_code'] ?: 'Đơn Gara') ?></div>
            <div><strong>Khách hàng:</strong> <?= e($c['customer_name']) ?></div>
            <div><strong>Số điện thoại:</strong> <?= e($c['customer_phone']) ?></div>
            <div><strong>Ngày mua:</strong> <?= date('d/m/Y', strtotime($c['purchase_date'])) ?></div>
            <div><strong>Hạn bảo hành:</strong> <strong style="color:var(--navy-dark);"><?= date('d/m/Y', strtotime($c['warranty_end_date'])) ?></strong></div>
          </div>

          <div style="margin-top:14px; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0; font-size:12px; color:var(--ink-2); line-height:1.5;">
            <strong>Điều kiện bảo hành áp dụng:</strong> Lắp đặt đúng quy trình kỹ thuật, nạp gas đúng dung lượng tiêu chuẩn nhà sản xuất, sản phẩm không va đập móp vỡ do ngoại lực.
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/../partials/foot.php'; ?>
