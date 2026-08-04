<?php require __DIR__ . '/../partials/head.php'; ?>
<style>
.warranty-wrap {
  max-width: 800px;
  margin: 40px auto 60px;
  padding: 0 16px;
}
.warranty-card {
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.06);
  border: 1px solid var(--line);
  padding: 32px;
}
.warranty-header {
  text-align: center;
  margin-bottom: 28px;
}
.warranty-header h1 {
  font-size: 24px;
  font-weight: 800;
  color: var(--navy-dark);
  margin: 0 0 8px;
}
.warranty-header p {
  font-size: 13.5px;
  color: var(--ink-2);
}
.search-form-row {
  display: flex;
  gap: 10px;
  margin-bottom: 24px;
}
@media (max-width: 500px) {
  .search-form-row { flex-direction: column; }
}
.search-form-row input {
  flex: 1;
  height: 48px;
  border-radius: 10px;
  border: 1px solid var(--line);
  padding: 0 16px;
  font-size: 14.5px;
  font-weight: 700;
  outline: none;
}
.search-form-row button {
  height: 48px;
  padding: 0 24px;
  background: var(--orange-accent);
  color: #ffffff;
  border-radius: 10px;
  border: none;
  font-weight: 800;
  font-size: 14.5px;
  cursor: pointer;
}
.warranty-result-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px;
  margin-top: 20px;
}
.status-badge-ok {
  background: #dcfce7;
  color: #15803d;
  font-weight: 800;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  display: inline-block;
}
.status-badge-expired {
  background: #fef2f2;
  color: #b91c1c;
  font-weight: 800;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  display: inline-block;
}
</style>

<div class="warranty-wrap">
  <div class="warranty-card">
    <div class="warranty-header">
      <h1>TRA CỨU PHIẾU BẢO HÀNH ĐIỆN TỬ</h1>
      <p>Nhập Mã Serial, Số điện thoại hoặc Mã đơn hàng để tra cứu thời hạn bảo hành phụ tùng</p>
    </div>

    <form method="get" action="/warranty/lookup" class="search-form-row">
      <input type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" required placeholder="VD: Serial, 0912345678 hoặc CS-2026-8888...">
      <button type="submit">TRA CỨU</button>
    </form>

    <?php if (isset($_GET['q'])): ?>
      <?php if (empty($cases)): ?>
        <div style="text-align:center; padding:30px 10px; color:var(--ink-2);">
           Không tìm thấy thông tin phiếu bảo hành khớp với từ khóa "<strong><?= e($_GET['q']) ?></strong>".<br>
          <small>Vui lòng kiểm tra lại Mã Serial trên vỏ hộp hoặc liên hệ Hotline Bảo hành: <strong>0704.0704.18</strong></small>
        </div>
      <?php else: ?>
        <h3 style="font-size:15px; font-weight:800; color:var(--navy-dark); margin-bottom:14px;">KẾT QUẢ TRA CỨU (<?= count($cases) ?> PHIẾU):</h3>
        
        <?php foreach ($cases as $c): ?>
          <?php
            $isExpired = strtotime($c['warranty_end_date']) < time();
            $daysLeft = ceil((strtotime($c['warranty_end_date']) - time()) / 86400);
          ?>
          <div class="warranty-result-box">
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

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; font-size:13px; color:var(--ink-1); border-top:1px solid #e2e8f0; padding-top:12px;">
              <div><strong>Serial Number:</strong> <?= e($c['serial_no'] ?: 'Tem điện tử hệ thống') ?></div>
              <div><strong>Mã đơn hàng:</strong> <?= e($c['order_code'] ?: 'Đơn Gara') ?></div>
              <div><strong>Khách hàng:</strong> <?= e($c['customer_name']) ?></div>
              <div><strong>Số điện thoại:</strong> <?= e($c['customer_phone']) ?></div>
              <div><strong>Ngày mua:</strong> <?= date('d/m/Y', strtotime($c['purchase_date'])) ?></div>
              <div><strong>Hạn bảo hành:</strong> <strong style="color:var(--navy-dark);"><?= date('d/m/Y', strtotime($c['warranty_end_date'])) ?></strong></div>
            </div>

            <div style="margin-top:14px; background:#fff; padding:12px; border-radius:8px; border:1px solid #e2e8f0; font-size:12px; color:var(--ink-2); line-height:1.5;">
              📌 <strong>Điều kiện bảo hành áp dụng:</strong> Lắp đặt đúng quy trình kỹ thuật, nạp gas đúng dung lượng tiêu chuẩn nhà sản xuất, sản phẩm không va đập móp vỡ do ngoại lực.
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</div>

<?php require __DIR__ . '/../partials/foot.php'; ?>
