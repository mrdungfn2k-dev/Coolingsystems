<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Phiên kiểm kho #<?= e($stockCount['code'] ?? '') ?></h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Kho: <strong><?= e($stockCount['warehouse_name'] ?? 'Kho chính') ?></strong> | Ngày tạo: <?= !empty($stockCount['created_at']) ? date('d/m/Y H:i', strtotime($stockCount['created_at'])) : '' ?></p>
  </div>
  <div style="display:flex;gap:10px">
    <a href="/admin/stock-counts" class="btn btn-outline">‹ Danh sách</a>
  </div>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.sc-table{width:100%;border-collapse:collapse;background:#fff}
.sc-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.sc-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
.sc-input-qty{width:90px;height:34px;border:1px solid #cbd5e1;border-radius:6px;padding:0 8px;text-align:center;font-weight:700;font-size:14px}
.sc-diff-pos{color:#16a34a;font-weight:700}
.sc-diff-neg{color:#dc2626;font-weight:700}
.sc-diff-zero{color:#94a3b8;font-weight:500}
</style>

<div style="background:#fff;padding:16px 20px;border:1px solid #e6ebf1;border-radius:8px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
  <div>
    <span style="font-size:13px;color:#64748b">Trạng thái:</span>
    <?php 
      $st = $stockCount['status'] ?? 'draft';
      $lbl = ['draft'=>'Nháp','in_progress'=>'Đang kiểm đếm','completed'=>'Đã hoàn tất / Cân bằng','cancelled'=>'Đã hủy'][$st] ?? $st;
    ?>
    <strong style="margin-left:6px;font-size:14px;color:#1a3258"><?= e($lbl) ?></strong>
    <?php if(!empty($stockCount['note'])): ?>
    <div style="font-size:12px;color:#64748b;margin-top:4px">Ghi chú: <?= e($stockCount['note']) ?></div>
    <?php endif; ?>
  </div>

  <?php if($st !== 'completed' && $st !== 'cancelled'): ?>
  <div style="display:flex;gap:10px">
    <button type="submit" form="stockCountForm" name="action_type" value="save" class="btn btn-outline">Lưu tiến độ</button>
    <button type="submit" form="stockCountForm" name="action_type" value="complete" class="btn btn-navy" onclick="return confirm('Bạn có chắc chắn muốn CÂN BẰNG TỒN KHO? Tồn kho sản phẩm trên hệ thống sẽ được cập nhật chính xác theo số lượng thực tế đếm được.')">✔ Cân bằng tồn kho & Hoàn tất</button>
  </div>
  <?php endif; ?>
</div>

<form id="stockCountForm" method="post" action="/admin/stock-counts/<?= (int)$stockCount['id'] ?>">
  <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">

  <div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
  <table class="sc-table">
    <thead>
      <tr>
        <th style="width:50px">STT</th>
        <th>Sản phẩm</th>
        <th>Mã SKU / OEM</th>
        <th>Vị trí kho</th>
        <th style="text-align:center">Tồn lý thuyết</th>
        <th style="text-align:center">Thực tế đếm được</th>
        <th style="text-align:center">Chênh lệch</th>
        <th>Lý do chênh lệch</th>
      </tr>
    </thead>
    <tbody>
      <?php $i=1; foreach($items as $it): 
        $sysQty = (int)$it['system_qty'];
        $actQty = isset($it['actual_qty']) ? (int)$it['actual_qty'] : $sysQty;
        $diff = $actQty - $sysQty;
      ?>
      <tr>
        <td style="color:#94a3b8;font-size:12px"><?= $i++ ?></td>
        <td>
          <strong style="color:#1a3258"><?= e($it['product_name'] ?? '') ?></strong>
        </td>
        <td style="font-family:monospace;font-size:12px;color:#64748b"><?= e($it['sku'] ?? $it['oem_code'] ?? '—') ?></td>
        <td style="font-size:12px;color:#0284c7;font-weight:600"><?= e($it['location_code'] ?? '—') ?></td>
        <td style="text-align:center;font-weight:700;color:#334155"><?= number_format($sysQty) ?></td>
        <td style="text-align:center">
          <?php if($st === 'completed' || $st === 'cancelled'): ?>
            <strong style="font-size:14px"><?= number_format($actQty) ?></strong>
          <?php else: ?>
            <input type="number" name="items[<?= (int)$it['id'] ?>][actual_qty]" value="<?= $actQty ?>" min="0" class="sc-input-qty" oninput="calcDiff(this, <?= $sysQty ?>)">
          <?php endif; ?>
        </td>
        <td style="text-align:center" class="diff-cell">
          <?php if($diff > 0): ?>
            <span class="sc-diff-pos">+<?= number_format($diff) ?></span>
          <?php elseif($diff < 0): ?>
            <span class="sc-diff-neg"><?= number_format($diff) ?></span>
          <?php else: ?>
            <span class="sc-diff-zero">0</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if($st === 'completed' || $st === 'cancelled'): ?>
            <span style="font-size:12px;color:#64748b"><?= e($it['reason'] ?? '—') ?></span>
          <?php else: ?>
            <input type="text" name="items[<?= (int)$it['id'] ?>][reason]" value="<?= e($it['reason'] ?? '') ?>" placeholder="Lý do chênh lệch (nếu có)..." style="width:100%;height:34px;border:1px solid #cbd5e1;border-radius:6px;padding:0 8px;font-size:12px">
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</form>

<script>
function calcDiff(input, sysQty) {
  var actQty = parseInt(input.value) || 0;
  var diff = actQty - sysQty;
  var row = input.closest('tr');
  var cell = row.querySelector('.diff-cell');
  if (diff > 0) {
    cell.innerHTML = '<span class="sc-diff-pos">+' + diff + '</span>';
  } else if (diff < 0) {
    cell.innerHTML = '<span class="sc-diff-neg">' + diff + '</span>';
  } else {
    cell.innerHTML = '<span class="sc-diff-zero">0</span>';
  }
}
</script>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
