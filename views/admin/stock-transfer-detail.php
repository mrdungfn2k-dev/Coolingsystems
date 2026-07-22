<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Phiếu chuyển kho #<?= e($transfer['code'] ?? '') ?></h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Từ: <strong><?= e($transfer['from_warehouse'] ?? 'Kho chính') ?></strong> ➔ Đến: <strong><?= e($transfer['to_warehouse'] ?? 'Chi nhánh') ?></strong></p>
  </div>
  <a href="/admin/stock-transfers" class="btn btn-outline">‹ Trở lại danh sách</a>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.st-table{width:100%;border-collapse:collapse;background:#fff}
.st-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.st-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
</style>

<div style="background:#fff;padding:20px;border:1px solid #e6ebf1;border-radius:10px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
  <div>
    <div style="font-size:13px;color:#64748b">Trạng thái phiếu:</div>
    <?php 
      $st = $transfer['status'] ?? 'pending';
      $lbl = ['pending'=>'Chờ xuất hàng','shipping'=>'Đang vận chuyển','completed'=>'Đã nhận / Hoàn tất','cancelled'=>'Đã hủy'][$st] ?? $st;
    ?>
    <strong style="font-size:16px;color:#1a3258"><?= e($lbl) ?></strong>
    <?php if(!empty($transfer['note'])): ?>
    <div style="font-size:12px;color:#64748b;margin-top:4px">Ghi chú: <?= e($transfer['note']) ?></div>
    <?php endif; ?>
  </div>

  <?php if($st !== 'completed' && $st !== 'cancelled'): ?>
  <div style="display:flex;gap:10px">
    <?php if($st === 'pending'): ?>
    <form method="post" action="/admin/stock-transfers/<?= (int)$transfer['id'] ?>/status">
      <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
      <input type="hidden" name="status" value="shipping">
      <button class="btn btn-navy" type="submit">🚚 Xuất kho & Vận chuyển</button>
    </form>
    <?php endif; ?>

    <?php if($st === 'shipping'): ?>
    <form method="post" action="/admin/stock-transfers/<?= (int)$transfer['id'] ?>/status">
      <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
      <input type="hidden" name="status" value="completed">
      <button class="btn btn-navy" type="submit" onclick="return confirm('Xác nhận ĐÃ NHẬN HÀNG VÀ CỘNG TỒN KHO vào kho nhận?')">✔ Nhận hàng & Hoàn tất</button>
    </form>
    <?php endif; ?>

    <form method="post" action="/admin/stock-transfers/<?= (int)$transfer['id'] ?>/status">
      <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
      <input type="hidden" name="status" value="cancelled">
      <button class="btn btn-outline" type="submit" onclick="return confirm('Bạn có chắc muốn HỦY phiếu chuyển kho này?')">Hủy phiếu</button>
    </form>
  </div>
  <?php endif; ?>
</div>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px;background:#fff">
<table class="st-table">
  <thead>
    <tr>
      <th style="width:50px">STT</th>
      <th>Sản phẩm phụ tùng</th>
      <th>Mã SKU / OEM</th>
      <th style="text-align:center">Số lượng điều chuyển</th>
    </tr>
  </thead>
  <tbody>
    <?php $i=1; foreach($items as $it): ?>
    <tr>
      <td style="color:#94a3b8;font-size:12px"><?= $i++ ?></td>
      <td>
        <strong style="color:#1a3258"><?= e($it['product_name'] ?? '') ?></strong>
      </td>
      <td style="font-family:monospace;font-size:12px;color:#64748b"><?= e($it['sku'] ?? $it['oem_code'] ?? '—') ?></td>
      <td style="text-align:center;font-weight:700;font-size:15px;color:#1e3a8a"><?= number_format((int)$it['quantity']) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
