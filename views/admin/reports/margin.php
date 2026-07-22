<?php require __DIR__.'/../../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Báo cáo Lợi nhuận gộp theo SKU (Margin Report)</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Phân tích biên lợi nhuận, doanh thu và giá vốn từng mặt hàng phụ tùng.</p>
  </div>
  <a href="/admin/reports/margin/export-csv?category_id=<?= (int)$catId ?>" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV / Excel</a>
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

<form class="rpt-filter" method="get" action="/admin/reports/margin">
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
    <button class="btn btn-navy" type="submit">Lọc báo cáo</button>
  </div>
</form>

<div class="rpt-summary">
  <div class="rpt-card">
    <div class="rpt-card-title">Tổng Doanh thu ước tính</div>
    <div class="rpt-card-val" style="color:#1e3a8a"><?= number_format($totalRevenue) ?> đ</div>
  </div>
  <div class="rpt-card">
    <div class="rpt-card-title">Tổng Giá vốn (COGS)</div>
    <div class="rpt-card-val" style="color:#64748b"><?= number_format($totalCost) ?> đ</div>
  </div>
  <div class="rpt-card">
    <div class="rpt-card-title">Lợi nhuận gộp (Gross Profit)</div>
    <div class="rpt-card-val" style="color:#16a34a"><?= number_format($totalProfit) ?> đ</div>
  </div>
  <div class="rpt-card">
    <div class="rpt-card-title">Tỷ lệ Biên lợi nhuận trung bình</div>
    <div class="rpt-card-val" style="color:#d97706"><?= $avgMargin ?>%</div>
  </div>
</div>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
<table class="rpt-table">
  <thead>
    <tr>
      <th>STT</th>
      <th>Sản phẩm phụ tùng</th>
      <th>Mã SKU</th>
      <th style="text-align:right">Đơn giá bán</th>
      <th style="text-align:right">Giá vốn đơn vị</th>
      <th style="text-align:center">Đã bán</th>
      <th style="text-align:right">Tổng Doanh thu</th>
      <th style="text-align:right">Lợi nhuận gộp</th>
      <th style="text-align:center">Biên Lợi Nhuận (%)</th>
    </tr>
  </thead>
  <tbody>
    <?php $i=1; foreach($items as $it): 
      $price = (int)$it['price'];
      $cost = (int)($it['cost_price'] ?: $price*0.7);
      $sold = (int)$it['sold_count'];
      $rev = $sold * $price;
      $cogs = $sold * $cost;
      $profit = $rev - $cogs;
      $margin = $rev > 0 ? round(($profit / $rev)*100, 1) : 0;
    ?>
    <tr>
      <td style="color:#94a3b8;font-size:12px"><?= $i++ ?></td>
      <td><strong style="color:#1a3258"><?= e($it['name']) ?></strong></td>
      <td style="font-family:monospace;font-size:12px;color:#64748b"><?= e($it['sku'] ?: '—') ?></td>
      <td style="text-align:right"><?= number_format($price) ?> đ</td>
      <td style="text-align:right;color:#64748b"><?= number_format($cost) ?> đ</td>
      <td style="text-align:center;font-weight:700"><?= number_format($sold) ?></td>
      <td style="text-align:right;font-weight:700;color:#1e3a8a"><?= number_format($rev) ?> đ</td>
      <td style="text-align:right;font-weight:700;color:#16a34a"><?= number_format($profit) ?> đ</td>
      <td style="text-align:center">
        <span style="padding:2px 8px;border-radius:4px;font-weight:700;font-size:12px;background:<?= $margin>=30?'#dcfce7':($margin>=15?'#fef9c3':'#fee2e2') ?>;color:<?= $margin>=30?'#166534':($margin>=15?'#854d0e':'#991b1b') ?>">
          <?= $margin ?>%
        </span>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php if(isset($totalPages) && $totalPages > 1): $base = ['category_id'=>$catId?:null]; ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/reports/margin?<?= e(http_build_query(array_filter($base+['page'=>$page-1]))) ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/reports/margin?<?= e(http_build_query(array_filter($base+['page'=>$page+1]))) ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__.'/../../partials/dashboard-foot.php'; ?>
