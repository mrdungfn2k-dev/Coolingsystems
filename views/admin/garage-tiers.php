<?php require __DIR__ . '/../partials/dashboard-head.php'; ?>

<div class="dash-head">
  <div>
    <h1>Cấu Hình Hạng Gara &amp; Mức Chiết Khấu Sỉ</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Thiết lập các cấp độ Hạng Gara và mức tỷ lệ % chiết khấu sỉ tự động cho từng hạng khách hàng B2B.</p>
  </div>
</div>

    <?php if (hasFlash('success')): ?>
      <div style="background:#dcfce7;color:#15803d;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-weight:700;font-size:14px">
        <?= getFlash('success') ?>
      </div>
    <?php endif; ?>

    <form method="post" action="/admin/garage-tiers/update">
      <?= csrfField() ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:20px;margin-bottom:24px">
        <?php foreach ($tiers as $idx => $t): ?>
          <div style="background:#fff;border-radius:12px;padding:20px;border:1px solid #cbd5e1;box-shadow:0 4px 12px rgba(0,0,0,0.03)">
            <input type="hidden" name="tier_id[]" value="<?= $t['id'] ?>">
            <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;margin-bottom:6px">Hạng Mã: <?= e($t['tier_code']) ?></div>
            
            <div style="margin-bottom:14px">
              <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:4px">Tên Hạng Gara</label>
              <input type="text" name="tier_name[]" value="<?= e($t['tier_name']) ?>" required style="width:100%;height:38px;border-radius:6px;border:1px solid #cbd5e1;padding:0 10px;font-size:13.5px;box-sizing:border-box">
            </div>

            <div style="margin-bottom:14px">
              <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:4px">Tỷ Lệ Chiết Khấu Sỉ (%)</label>
              <input type="number" step="0.5" min="0" max="50" name="discount_percent[]" value="<?= e($t['discount_percent']) ?>" required style="width:100%;height:38px;border-radius:6px;border:1px solid #cbd5e1;padding:0 10px;font-size:13.5px;box-sizing:border-box">
            </div>

            <div style="margin-bottom:10px">
              <label style="display:block;font-size:13px;font-weight:700;color:#1e293b;margin-bottom:4px">Doanh Số Chi Tiêu Hàng Tháng Tối Thiểu (VNĐ)</label>
              <input type="number" step="1000000" min="0" name="min_monthly_spend[]" value="<?= e($t['min_monthly_spend']) ?>" required style="width:100%;height:38px;border-radius:6px;border:1px solid #cbd5e1;padding:0 10px;font-size:13.5px;box-sizing:border-box">
              <div style="font-size:11.5px;color:#64748b;margin-top:3px"><?= vnd($t['min_monthly_spend']) ?> / tháng</div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div style="background:#fff;border-radius:12px;padding:20px;border:1px solid #cbd5e1;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div style="font-size:13px;color:#475569">
          Ghi chú: Khi tài khoản Gara được xác thực, hệ thống sẽ ưu tiên áp dụng tỷ lệ chiết khấu theo Hạng Gara đã chọn.
        </div>
        <button type="submit" style="background:#0b1d3a;color:#fff;font-weight:800;font-size:14px;padding:10px 24px;border-radius:8px;border:none;cursor:pointer">
          LƯU CẤU HÌNH HẠNG GARA
        </button>
      </div>
    </form>

<?php require __DIR__ . '/../partials/dashboard-foot.php'; ?>
