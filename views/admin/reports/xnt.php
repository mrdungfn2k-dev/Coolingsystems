<?php require __DIR__.'/../../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Báo cáo Xuất - Nhập - Tồn (XNT)</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Đối soát chi tiết biến động tồn kho: Tồn đầu kỳ, Tổng nhập, Tổng xuất và Tồn cuối kỳ.</p>
  </div>
  <a href="/admin/reports/xnt/export-csv?from=<?= e($fromDate) ?>&to=<?= e($toDate) ?>&category_id=<?= (int)$catId ?>&q=<?= urlencode($q ?? '') ?>" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV / Excel</a>
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
  <div style="position:relative;flex:1;min-width:260px">
    <span style="font-size:12px;color:#64748b;display:block;margin-bottom:4px">Tìm kiếm sản phẩm phụ tùng:</span>
    <input type="text" id="xntSearchInput" name="q" value="<?= e($q ?? '') ?>" placeholder="Nhập tên phụ tùng, SKU hoặc OEM hiện gợi ý..." autocomplete="off" style="width:100%">
    <div id="xntSuggestBox" style="display:none;position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #cbd5e1;border-radius:6px;box-shadow:0 6px 20px rgba(0,0,0,0.15);z-index:9999;max-height:280px;overflow-y:auto;margin-top:4px"></div>
  </div>
  <div style="margin-top:18px;display:flex;gap:6px">
    <button class="btn btn-navy" type="submit">Xem báo cáo</button>
    <?php if(!empty($q) || $catId > 0): ?>
    <a href="/admin/reports/xnt" class="btn btn-outline" style="height:38px;display:inline-flex;align-items:center">Xóa lọc</a>
    <?php endif; ?>
  </div>
</form>

<div class="rpt-summary">
  <div class="rpt-card">
    <div class="rpt-card-title">Tổng mã sản phẩm</div>
    <div class="rpt-card-val"><?= number_format($totalProducts) ?></div>
  </div>
  <div class="rpt-card">
    <div class="rpt-card-title">Tổng xuất kho</div>
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
      <th style="width:45px">STT</th>
      <th>Sản phẩm phụ tùng</th>
      <th>Mã SKU / OEM</th>
      <th>Vị trí kho</th>
      <th style="text-align:right">Tồn đầu kỳ (ước tính)</th>
      <th style="text-align:right">Đã nhập</th>
      <th style="text-align:right">Đã xuất kho</th>
      <th style="text-align:right">Tồn cuối kỳ</th>
      <th style="text-align:right">Giá trị tồn kho</th>
    </tr>
  </thead>
  <tbody>
    <?php $stt = ($page - 1) * 25 + 1; foreach($items as $it): 
      $st = (int)$it['stock'];
      $sd = (int)$it['sold_count'];
      $startStock = $st + $sd; 
      $cost = (int)($it['cost_price'] ?: $it['price']*0.7);
      $val = $st * $cost;
    ?>
    <tr>
      <td style="color:#94a3b8;font-size:12px"><?= $stt++ ?></td>
      <td style="font-weight:700;color:#1a3258">
        <?= e($it['name']) ?>
      </td>
      <td>
        <span style="font-family:monospace;font-size:12px;color:#475569"><?= e($it['sku'] ?: '—') ?></span>
        <?php if($it['oem_code']): ?>
        <div style="font-size:11px;color:#0284c7">OEM: <?= e($it['oem_code']) ?></div>
        <?php endif; ?>
      </td>
      <td>
        <?php if($it['location_code']): ?>
        <span style="background:#e0f2fe;color:#0369a1;padding:2px 6px;border-radius:4px;font-size:11px;font-family:monospace"><?= e($it['location_code']) ?></span>
        <?php else: ?>
        <span style="color:#cbd5e1">—</span>
        <?php endif; ?>
      </td>
      <td style="text-align:right;color:#64748b"><?= number_format($startStock) ?></td>
      <td style="text-align:right;color:#16a34a;font-weight:600">+<?= number_format($startStock) ?></td>
      <td style="text-align:right;color:#dc2626;font-weight:600">-<?= number_format($sd) ?></td>
      <td style="text-align:right;font-weight:800;color:#1a3258"><?= number_format($st) ?></td>
      <td style="text-align:right;font-weight:700;color:#16a34a"><?= number_format($val) ?> đ</td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$items): ?>
    <tr><td colspan="9" style="padding:30px;text-align:center;color:#94a3b8">Không tìm thấy sản phẩm phụ tùng nào khớp với bộ lọc.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php if(isset($totalPages) && $totalPages > 1): ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/reports/xnt?from=<?= e($fromDate) ?>&to=<?= e($toDate) ?>&category_id=<?= (int)$catId ?>&q=<?= urlencode($q ?? '') ?>&page=<?= $page-1 ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/reports/xnt?from=<?= e($fromDate) ?>&to=<?= e($toDate) ?>&category_id=<?= (int)$catId ?>&q=<?= urlencode($q ?? '') ?>&page=<?= $page+1 ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<script>
(function(){
  var inp = document.getElementById('xntSearchInput');
  var box = document.getElementById('xntSuggestBox');
  if(!inp || !box) return;
  var timer = null;
  inp.addEventListener('input', function(){
    clearTimeout(timer);
    var q = this.value.trim();
    if(q.length < 2) { box.style.display = 'none'; box.innerHTML = ''; return; }
    timer = setTimeout(function(){
      fetch('/admin/inventory/search-product?q=' + encodeURIComponent(q))
        .then(function(r){ return r.json(); })
        .then(function(res){
          var items = Array.isArray(res) ? res : (res.items || []);
          if(!items.length) {
            box.innerHTML = '<div style="padding:10px;font-size:12px;color:#94a3b8;text-align:center">Không tìm thấy sản phẩm phù hợp</div>';
            box.style.display = 'block';
            return;
          }
          var html = '';
          items.forEach(function(item){
            html += '<div class="xnt-suggest-item" style="padding:9px 12px;border-bottom:1px solid #f1f5f9;cursor:pointer;font-size:13px" data-name="' + (item.name||'') + '">';
            html += '<strong style="color:#1e293b;display:block">' + (item.name||'') + '</strong>';
            html += '<span style="font-size:11px;color:#64748b">Mã SKU: ' + (item.sku||'—') + ' | OEM: ' + (item.oem_code||'—') + ' | Tồn: ' + (item.stock||0) + '</span>';
            html += '</div>';
          });
          box.innerHTML = html;
          box.style.display = 'block';
          
          box.querySelectorAll('.xnt-suggest-item').forEach(function(el){
            el.addEventListener('mouseenter', function(){ this.style.background = '#f8fafc'; });
            el.addEventListener('mouseleave', function(){ this.style.background = '#ffffff'; });
            el.addEventListener('click', function(){
              inp.value = this.getAttribute('data-name');
              box.style.display = 'none';
              inp.form.submit();
            });
          });
        });
    }, 250);
  });
  document.addEventListener('click', function(e){
    if(!inp.contains(e.target) && !box.contains(e.target)) box.style.display = 'none';
  });
})();
</script>

<?php require __DIR__.'/../../partials/dashboard-foot.php'; ?>
