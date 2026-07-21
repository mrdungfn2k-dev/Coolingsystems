<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Garage khách hàng</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Danh sách xe của khách hàng đã đăng ký trên hệ thống.</p>
  </div>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.garage-filter{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}
.garage-filter input,.garage-filter select{height:38px;border:1px solid #d8e0ea;border-radius:6px;padding:0 10px;background:#fff;font-size:13px}
.garage-table{width:100%;border-collapse:collapse;background:#fff}
.garage-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.garage-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
.garage-table tr:hover td{background:#f9fbff}
.garage-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:18px}
.garage-kpi{border:1px solid #e3e9f1;background:#fff;padding:16px;border-radius:8px}
.garage-kpi b{display:block;font-size:22px;font-weight:800;color:#17325c}
.garage-kpi span{font-size:12px;color:#718096}
.car-badge{display:inline-flex;align-items:center;gap:5px;background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;border-radius:4px;padding:3px 8px;font-size:12px;font-weight:600}
</style>

<div class="garage-kpis">
  <div class="garage-kpi"><b><?= number_format($summary['total_garages']) ?></b><span>Tổng số xe đã đăng ký</span></div>
  <div class="garage-kpi"><b><?= number_format($summary['total_owners']) ?></b><span>Khách hàng có xe</span></div>
  <div class="garage-kpi"><b><?= number_format($summary['default_count']) ?></b><span>Xe mặc định</span></div>
</div>

<form class="garage-filter" method="get" action="/admin/garages">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Tìm tên KH, email, hãng xe..." style="min-width:260px">
  <select name="brand_id">
    <option value="0">Tất cả hãng xe</option>
    <?php foreach($brands as $b): ?>
    <option value="<?= (int)$b['id'] ?>" <?= $brandId===(int)$b['id']?'selected':'' ?>><?= e($b['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-navy" type="submit">Lọc</button>
  <?php if($q||$brandId): ?>
  <a href="/admin/garages" class="btn btn-outline">Xóa lọc</a>
  <?php endif; ?>
</form>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
<table class="garage-table">
  <thead>
    <tr>
      <th>#</th>
      <th>Khách hàng</th>
      <th>Xe</th>
      <th>Năm SX</th>
      <th>Nhãn xe</th>
      <th>Mặc định</th>
      <th>Ngày đăng ký</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($garages as $g): ?>
    <tr>
      <td style="color:#9ca3af;font-size:12px">#<?= (int)$g['id'] ?></td>
      <td>
        <strong style="color:#1f365b"><?= e($g['full_name'] ?: '—') ?></strong>
        <div style="font-size:11px;color:#718096;margin-top:2px"><?= e($g['email'] ?: '') ?></div>
        <?php if($g['phone']): ?>
        <div style="font-size:11px;color:#718096"><?= e($g['phone']) ?></div>
        <?php endif; ?>
      </td>
      <td>
        <span class="car-badge"><?= e($g['brand_name'] ?: '?') ?></span>
        <span style="margin-left:5px;color:#374151;font-weight:500"><?= e($g['model_name'] ?: '?') ?></span>
        <?php if($g['trim']): ?><div style="font-size:11px;color:#9ca3af;margin-top:2px"><?= e($g['trim']) ?></div><?php endif; ?>
      </td>
      <td style="font-weight:600;color:#374151"><?= (int)$g['year'] ?></td>
      <td style="color:#4b5563"><?= e($g['label'] ?: '—') ?></td>
      <td style="text-align:center">
        <?php if($g['is_default']): ?>
        <span style="background:#dcfce7;color:#166534;border:1px solid #bbf7d0;border-radius:4px;padding:2px 8px;font-size:11px;font-weight:700">Mặc định</span>
        <?php else: ?>
        <span style="color:#9ca3af;font-size:11px">—</span>
        <?php endif; ?>
      </td>
      <td style="color:#9ca3af;font-size:12px;white-space:nowrap"><?= e(substr($g['created_at'],0,10)) ?></td>
      <td>
        <a href="/admin/users/<?= (int)$g['user_id'] ?>" style="font-size:12px;color:#1a3258;text-decoration:none;border:1px solid #1a3258;border-radius:4px;padding:4px 10px;white-space:nowrap;font-weight:600">Hồ sơ KH</a>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$garages): ?>
    <tr><td colspan="8" style="padding:30px;text-align:center;color:#9ca3af">Không tìm thấy xe nào phù hợp.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php if($totalPages > 1): $base = ['q'=>$q,'brand_id'=>$brandId?:null]; ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/garages?<?= e(http_build_query(array_filter($base+['page'=>$page-1]))) ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/garages?<?= e(http_build_query(array_filter($base+['page'=>$page+1]))) ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
