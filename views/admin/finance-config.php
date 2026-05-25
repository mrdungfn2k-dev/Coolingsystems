<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="sec-card">
  <div class="dash-head"><h1 style="margin:0">Cấu hình Vận chuyển & Giảm giá</h1></div>
  <div class="panel-body" style="padding:24px 28px">
    <form method="post" action="/admin/settings/finance">
      <?= csrfField() ?>

      <h3 style="font-size:16px;color:var(--navy);margin:0 0 16px;font-weight:700">1. Thiết lập Vận chuyển</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px">
        <div>
          <label class="frm-lbl">Phí vận chuyển mặc định (VNĐ)</label>
          <input type="number" name="default_shipping_fee" class="frm-input" value="<?= $config['default_shipping_fee']??30000 ?>">
        </div>
        <div>
          <label class="frm-lbl">Miễn phí vận chuyển cho đơn từ (VNĐ)</label>
          <input type="number" name="free_shipping_threshold" class="frm-input" value="<?= $config['free_shipping_threshold']??2000000 ?>">
        </div>
      </div>

      <h3 style="font-size:16px;color:var(--navy);margin:0 0 16px;font-weight:700">2. Quy tắc Giảm giá số lượng</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:8px">
        <div>
          <label class="frm-lbl">Mua đạt số lượng SP (Cái)</label>
          <input type="number" name="discount_quantity_threshold" class="frm-input" value="<?= $config['discount_quantity_threshold']??0 ?>" min="0">
          <small style="color:#888">Điền 0 nếu không áp dụng.</small>
        </div>
        <div>
          <label class="frm-lbl">Mức giảm giá (%)</label>
          <input type="number" name="discount_quantity_percent" class="frm-input" value="<?= $config['discount_quantity_percent']??0 ?>" min="0" max="100" step="0.1">
        </div>
      </div>
      <div style="background:#f0f4ff;border-radius:8px;padding:14px 16px;margin-bottom:24px;font-size:13px;color:#4a6fa5">
        <strong>Ví dụ:</strong> Nếu thiết lập "Mua đạt 5 SP" và "Mức giảm 10%", khi khách mua ≥ 5 sản phẩm trong 1 đơn, hệ thống sẽ tự động giảm 10% trên tổng tiền hàng.
      </div>

      <div style="text-align:right">
        <button type="submit" class="btn btn-navy">Lưu cấu hình</button>
      </div>
    </form>
  </div>
</div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>