<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="sec-card">
  <div class="dash-head"><h1 style="margin:0">Cấu hình Vận chuyển</h1></div>
  <div class="panel-body" style="padding:24px 28px">
    <form method="post" action="/admin/settings/finance">
      <?= csrfField() ?>

      <h3 style="font-size:16px;color:var(--navy);margin:0 0 16px;font-weight:700">1. Thiết lập Vận chuyển</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px">
        <div>
          <label class="frm-lbl">Phí vận chuyển dự phòng (VNĐ)</label>
          <input type="number" name="default_shipping_fee" class="frm-input" value="<?= $config['default_shipping_fee']??30000 ?>">
          <small style="color:#888">Dùng khi không xác định được vùng hoặc cân nặng.</small>
        </div>
        <div>
          <label class="frm-lbl">Miễn phí vận chuyển cho đơn từ (VNĐ)</label>
          <input type="number" name="free_shipping_threshold" class="frm-input" value="<?= $config['free_shipping_threshold']??2000000 ?>">
          <small style="color:#888">Để <b>0</b> nếu KHÔNG áp dụng miễn phí ship (luôn tính phí theo vùng + cân nặng).</small>
        </div>
      </div>

      <?php
        $shipRates = json_decode($config['shipping_rates'] ?? '[]', true); if(!is_array($shipRates)) $shipRates=[];
        $rateBy = []; foreach($shipRates as $rr){ $rateBy[$rr['zone'] ?? ''] = $rr; }
        $zoneLabels = ['noi_tinh'=>'Nội tỉnh (cùng tỉnh người gửi)','noi_mien'=>'Nội miền (cùng miền Bắc/Trung/Nam)','can_mien'=>'Cận miền (miền liền kề)','lien_mien'=>'Liên miền (Bắc ↔ Nam)'];
      ?>
      <h3 style="font-size:16px;color:var(--navy);margin:0 0 6px;font-weight:700">2. Phí theo Vùng miền &amp; Cân nặng (kiểu Viettel Post)</h3>
      <p style="color:#888;font-size:13px;margin:0 0 16px">Phí ship tự tính theo tỉnh người nhận + tổng khối lượng giỏ hàng (lấy từ khối lượng từng sản phẩm). Mỗi vùng có giá cho mức cân đầu, rồi cộng thêm theo từng nấc cân tiếp theo.</p>
      <div style="margin-bottom:18px;max-width:420px">
        <label class="frm-lbl">Tỉnh/Thành nơi gửi hàng (kho)</label>
        <select name="shipping_origin_province" class="frm-input"><?= vnProvinceOptions($config['shipping_origin_province'] ?? '') ?></select>
      </div>
      <div style="overflow-x:auto;margin-bottom:24px">
        <table class="tbl" style="width:100%;min-width:680px">
          <thead><tr style="background:#f8fafc">
            <th style="text-align:left;padding:10px">Vùng giao</th>
            <th style="padding:10px">Cân đầu (g)</th>
            <th style="padding:10px">Giá cân đầu (đ)</th>
            <th style="padding:10px">Mỗi nấc tiếp (g)</th>
            <th style="padding:10px">Giá mỗi nấc (đ)</th>
          </tr></thead>
          <tbody>
          <?php foreach($zoneLabels as $zc=>$zl): $r=$rateBy[$zc]??[]; ?>
            <tr>
              <td style="padding:8px 10px;font-weight:600;color:var(--navy)"><?= $zl ?></td>
              <td style="padding:8px 10px"><input type="number" min="1" name="rate_<?= $zc ?>_base_weight" class="frm-input" style="width:110px" value="<?= (int)($r['base_weight']??1000) ?>"></td>
              <td style="padding:8px 10px"><input type="number" min="0" name="rate_<?= $zc ?>_base_price" class="frm-input" style="width:130px" value="<?= (int)($r['base_price']??0) ?>"></td>
              <td style="padding:8px 10px"><input type="number" min="1" name="rate_<?= $zc ?>_step_weight" class="frm-input" style="width:110px" value="<?= (int)($r['step_weight']??500) ?>"></td>
              <td style="padding:8px 10px"><input type="number" min="0" name="rate_<?= $zc ?>_step_price" class="frm-input" style="width:130px" value="<?= (int)($r['step_price']??0) ?>"></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div style="text-align:right">
        <button type="submit" class="btn btn-navy">Lưu cấu hình</button>
      </div>
    </form>
  </div>
</div>
<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>