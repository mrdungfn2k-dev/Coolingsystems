<?php require __DIR__.'/../../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Báo cáo Xuất - Nhập - Tồn (XNT)</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Đối soát chi tiết biến động tồn kho: Tồn đầu kỳ, Tổng nhập, Tổng xuất và Tồn cuối kỳ.</p>
  </div>
</div>

<style>
.rpt-filter{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:20px;background:#fff;padding:16px;border:1px solid #e6ebf1;border-radius:8px}
.rpt-filter input,.rpt-filter select{height:38px;border:1px solid #d8e0ea;border-radius:6px;padding:0 10px;background:#fff;font-size:13px}
.rpt-table{width:100%;border-collapse:collapse;background:#fff}
.rpt-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.rpt-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
.rpt-summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:20px}
.rpt-card{background:#fff;padding:16px;border:1px solid #e6ebf1;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.02)}
.rpt-card-title{font-size:12px;color:#64748b;font-weight:600;text-transform:uppercase}
.rpt-card-val{font-size:20px;font-weight:800;color:#1a3258;margin-top:6px}
</style>

<form class="rpt-filter" method="get" action="/admin/reports/xnt">
  <div>
    <span style="font-size:12px;color:#64748b;display:block;margin-bottom:4px">Từ ngày:</span>
    <input type="date" name="from" value="<?= e($fromDate) ?>">
  </div>
  <div>
    <span style="font-size:12px;color:#64748b;display:block;margin-bottom:4px">Đến ngày:</span>
    <input type="date" name="to" value="<?= e($toDate) ?>">
  </div>
  <div>
    <span style="font-size:12px;color:#64748b;display:block;margin-bottom:4px">Danh mục:</span>
    <select name="category_id">
      <option value="0">Tất cả danh mục</option>
      <?php foreach($categories as $c): ?>
      <option value="<?= (int)$c['id'] ?>" <?= $catId===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div style="margin-top:18px">
    <button class="btn btn-navy" type="submit">Xem báo cáo</button>
  </div>
</form>

<div class="rpt-summary">
  <div class="rpt-card">
    <div class="rpt-card-title">Tổng mã sản phẩm</div>
    <div class="rpt-card-val"><?= number_format($totalProducts) ?></div>
  </div>
  <div class="rpt-card">
    <div class="rpt-card-title">Tổng xuất bán</div>
    <div class="rpt-card-val" style="color:#0284c7"><?= number_format($totalExported) ?></div>
  </div>
  <div class="rpt-card">
    <div class="rpt-card-title">Tổng tồn kho hiện tại</div>
    <div class="rpt-card-val" style="color:#16a34a"><?= number_format($totalStock) ?></div>
  </div>
  <div class="rpt-card">
    <div class="rpt-card-title">Tổng giá trị tồn kho</div>
    <div class="rpt-card-val" style="color:#1a3258"><?= number_format($totalStockValue) ?> đ</div>
  </div>
</div>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
<table class="rpt-table">
  <thead>
    <tr>
      <th>STT</th>
      <th>Sản phẩm phụ tùng</th>
      <th>Mã SKU / OEM</th>
      <th>Vị trí kho</th>
      <th style="text-align:center">Tồn đầu kỳ (ước tính)</th>
      <th style="text-align:center">Đã nhập</th>
      <th style="text-align:center">Đã xuất bán</th>
      <th style="text-align:center">Tồn cuối kỳ</th>
      <th style="text-align:right">Giá trị tồn kho</th>
    </tr>
  </thead>
  <tbody>
    <?php $i=1; foreach($items as $it): 
      $stock = (int)$it['stock'];
      $sold = (int)$it['sold_count'];
      $cost = (int)($it['cost_price'] ?: $it['price']*0.7);
      $stockVal = $stock * $cost;
      $initial = $stock + $sold;
    ?>
    <tr>
      <td style="color:#94a3b8;font-size:12px"><?= $i++ ?></td>
      <td><strong style="color:#1a3258"><?= e($it['name']) ?></strong></td>
      <td style="font-family:monospace;font-size:12px;color:#64748b"><?= e($it['sku'] ?: $it['oem_code'] ?: '—') ?></td>
      <td style="font-size:12px;color:#0284c7;font-weight:600"><?= e($it['location_code'] ?: '—') ?></td>
      <td style="text-align:center;color:#64748b"><?= number_format($initial) ?></td>
      <td style="text-align:center;color:#16a34a;font-weight:600">+<?= number_format($initial) ?></td>
      <td style="text-align:center;color:#dc2626;font-weight:600">-<?= number_format($sold) ?></td>
      <td style="text-align:center;font-weight:800;color:#1e3a8a"><?= number_format($stock) ?></td>
      <td style="text-align:right;font-weight:700;color:#1a3258"><?= number_format($stockVal) ?> đ</td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php require __DIR__.'/../../partials/dashboard-foot.php'; ?>
