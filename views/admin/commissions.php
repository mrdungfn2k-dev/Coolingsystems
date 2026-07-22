<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div>
    <h1>Hoa hồng bán hàng</h1>
    <p style="margin:4px 0 0;color:#718096;font-size:13px">Theo dõi hoa hồng giao dịch của các đối tác/cộng tác viên.</p>
  </div>
  <div style="display:flex;gap:10px;align-items:center">
    <button type="button" onclick="document.getElementById('importCommModal').style.display='flex'" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↑ Nhập CSV</button>
    <a href="/admin/commissions/export-csv" class="btn btn-outline-navy btn-sm" style="display:inline-flex;align-items:center;gap:4px">↓ Xuất CSV</a>
  </div>
</div>

<div id="importCommModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
  <form method="post" action="/admin/commissions/import-csv" enctype="multipart/form-data" style="background:#fff;padding:24px;border-radius:10px;max-width:450px;width:100%;box-shadow:0 10px 30px rgba(0,0,0,0.2)">
    <input type="hidden" name="_csrf" value="<?= csrfToken() ?>">
    <h3 style="margin:0 0 14px;color:#1a3258">Nhập hoa hồng từ file CSV</h3>
    <div style="margin-bottom:14px;font-size:12px;color:#64748b">
      Định dạng CSV: <code>Partner_ID, Type(earn/payout), Fee, Note</code>
    </div>
    <div style="margin-bottom:18px">
      <input type="file" name="csv_file" accept=".csv" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" onclick="document.getElementById('importCommModal').style.display='none'" class="btn btn-outline">Hủy</button>
      <button type="submit" class="btn btn-navy">Tải lên & Nhập</button>
    </div>
  </form>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.comm-filter{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}
.comm-filter input,.comm-filter select{height:38px;border:1px solid #d8e0ea;border-radius:6px;padding:0 10px;background:#fff;font-size:13px}
.comm-table{width:100%;border-collapse:collapse;background:#fff}
.comm-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:11px 10px;text-align:left;white-space:nowrap;border-bottom:2px solid #e6ebf1}
.comm-table td{padding:11px 10px;border-top:1px solid #edf1f5;vertical-align:middle;font-size:13px}
.comm-table tr:hover td{background:#f9fbff}
.comm-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
.comm-kpi{border:1px solid #e3e9f1;background:#fff;padding:16px;border-radius:8px}
.comm-kpi b{display:block;font-size:20px;font-weight:800;color:#17325c}
.comm-kpi span{font-size:12px;color:#718096}
.type-badge{display:inline-block;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700}
.type-earn{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}
.type-reversal{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca}
.type-adjustment{background:#fef9c3;color:#854d0e;border:1px solid #fde68a}
.status-settled{background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;border-radius:4px;padding:2px 7px;font-size:11px;font-weight:700}
.status-pending{background:#fef9c3;color:#854d0e;border:1px solid #fde68a;border-radius:4px;padding:2px 7px;font-size:11px;font-weight:700}
.status-disputed{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;border-radius:4px;padding:2px 7px;font-size:11px;font-weight:700}
</style>

<div class="comm-kpis">
  <div class="comm-kpi">
    <b><?= number_format($summary['total_txn']) ?></b>
    <span>Tổng giao dịch</span>
  </div>
  <div class="comm-kpi">
    <b style="color:#059669"><?= number_format($summary['total_earn']) ?> đ</b>
    <span>Tổng hoa hồng kiếm được</span>
  </div>
  <div class="comm-kpi">
    <b style="color:#c2410c"><?= number_format($summary['total_reversal']) ?> đ</b>
    <span>Tổng hoàn trả HH</span>
  </div>
  <div class="comm-kpi">
    <b><?= number_format($summary['partner_count']) ?></b>
    <span>Đối tác có giao dịch</span>
  </div>
</div>

<form class="comm-filter" method="get" action="/admin/commissions">
  <select name="partner_id">
    <option value="0">Tất cả đối tác</option>
    <?php foreach($partners as $pt): ?>
    <option value="<?= (int)$pt['id'] ?>" <?= $partnerId===(int)$pt['id']?'selected':'' ?>><?= e($pt['shop_name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="type">
    <option value="">Tất cả loại</option>
    <option value="earn" <?= $typeFilter==='earn'?'selected':'' ?>>Kiếm được</option>
    <option value="reversal" <?= $typeFilter==='reversal'?'selected':'' ?>>Hoàn trả</option>
    <option value="adjustment" <?= $typeFilter==='adjustment'?'selected':'' ?>>Điều chỉnh</option>
  </select>
  <select name="status">
    <option value="">Tất cả trạng thái</option>
    <option value="settled" <?= $statusFilter==='settled'?'selected':'' ?>>Đã quyết toán</option>
    <option value="pending" <?= $statusFilter==='pending'?'selected':'' ?>>Chờ quyết toán</option>
    <option value="disputed" <?= $statusFilter==='disputed'?'selected':'' ?>>Đang tranh chấp</option>
  </select>
  <input type="date" name="from" value="<?= e($fromDate) ?>" placeholder="Từ ngày">
  <input type="date" name="to" value="<?= e($toDate) ?>" placeholder="Đến ngày">
  <button class="btn btn-navy" type="submit">Lọc</button>
  <?php if($partnerId||$typeFilter||$statusFilter||$fromDate||$toDate): ?>
  <a href="/admin/commissions" class="btn btn-outline">Xóa lọc</a>
  <?php endif; ?>
</form>

<div style="overflow:auto;border:1px solid #e6ebf1;border-radius:8px">
<table class="comm-table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Đối tác</th>
      <th>Đơn hàng</th>
      <th>Loại</th>
      <th style="text-align:right">Giá trị đơn</th>
      <th style="text-align:right">Tỷ lệ HH</th>
      <th style="text-align:right">Hoa hồng</th>
      <th style="text-align:right">Thực nhận</th>
      <th>Trạng thái</th>
      <th>Ghi chú</th>
      <th>Ngày tạo</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($commissions as $c): ?>
    <tr>
      <td style="color:#9ca3af;font-size:12px">#<?= (int)$c['id'] ?></td>
      <td>
        <strong style="color:#1f365b"><?= e($c['shop_name'] ?: '—') ?></strong>
        <?php if($c['contact_phone']): ?>
        <div style="font-size:11px;color:#718096"><?= e($c['contact_phone']) ?></div>
        <?php endif; ?>
      </td>
      <td style="font-family:monospace;font-size:12px;color:#374151"><?= e($c['sub_order_id'] ? '#'.$c['sub_order_id'] : '—') ?></td>
      <td>
        <?php if($c['type']==='earn'): ?>
        <span class="type-badge type-earn">↑ Kiếm HH</span>
        <?php elseif($c['type']==='reversal'): ?>
        <span class="type-badge type-reversal">↓ Hoàn trả</span>
        <?php else: ?>
        <span class="type-badge type-adjustment">± Điều chỉnh</span>
        <?php endif; ?>
      </td>
      <td style="text-align:right;font-weight:500"><?= number_format((int)$c['gross_amount']) ?></td>
      <td style="text-align:right;color:#4b5563"><?= number_format((float)$c['commission_rate']*100,2) ?>%</td>
      <td style="text-align:right;font-weight:700;color:<?= $c['type']==='earn'?'#059669':'#dc2626' ?>">
        <?= $c['type']==='earn'?'+':'-' ?><?= number_format((int)$c['commission_fee']) ?> đ
      </td>
      <td style="text-align:right;font-weight:600;color:#374151"><?= number_format((int)$c['net_amount']) ?> đ</td>
      <td><span class="status-<?= e($c['status']) ?>"><?= ['settled'=>'Đã quyết toán','pending'=>'Chờ QT','disputed'=>'Tranh chấp'][$c['status']] ?? e($c['status']) ?></span></td>
      <td style="font-size:12px;color:#6b7280;max-width:200px"><?= e($c['note'] ?: '—') ?></td>
      <td style="font-size:12px;color:#9ca3af;white-space:nowrap"><?= e(substr($c['created_at'],0,10)) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$commissions): ?>
    <tr><td colspan="11" style="padding:30px;text-align:center;color:#9ca3af">Chưa có giao dịch hoa hồng nào.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php if($totalPages > 1): $base = ['partner_id'=>$partnerId?:null,'type'=>$typeFilter?:null,'status'=>$statusFilter?:null,'from'=>$fromDate?:null,'to'=>$toDate?:null]; ?>
<div class="pagination" style="margin-top:16px">
  <?php if($page>1): ?><a href="/admin/commissions?<?= e(http_build_query(array_filter($base+['page'=>$page-1]))) ?>">‹</a><?php endif; ?>
  <span style="padding:0 12px;font-size:13px">Trang <?= $page ?> / <?= $totalPages ?></span>
  <?php if($page<$totalPages): ?><a href="/admin/commissions?<?= e(http_build_query(array_filter($base+['page'=>$page+1]))) ?>">›</a><?php endif; ?>
</div>
<?php endif; ?>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
