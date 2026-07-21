<?php require __DIR__.'/../partials/dashboard-head.php'; ?>
<div class="dash-head">
  <div style="display:flex;align-items:center;gap:12px">
    <a href="/admin/users" class="btn btn-ghost" style="padding:6px 12px">← Danh sách</a>
    <div>
      <h1>Hồ sơ khách hàng</h1>
      <p style="color:#667085;margin:0">Xem thông tin chi tiết, đội xe, đơn hàng và lịch sử công nợ.</p>
    </div>
  </div>
</div>

<?php foreach(getFlash() as $x): ?>
<div class="alert alert-<?= e($x['type']) ?>"><?= e($x['message']) ?></div>
<?php endforeach; ?>

<style>
.profile-grid{display:grid;grid-template-columns:280px 1fr;gap:20px;margin-bottom:20px}
.profile-card{background:#fff;border:1px solid #e6ebf1;border-radius:8px;padding:20px;text-align:center}
.profile-avatar{width:90px;height:90px;border-radius:50%;background:#f0f0f0;margin:0 auto 15px;overflow:hidden;border:3px solid #1a3258}
.profile-avatar img{width:100%;height:100%;object-fit:cover}
.profile-info-list{text-align:left;margin-top:15px;font-size:13px}
.profile-info-item{margin-bottom:10px;color:#4a5568}
.profile-info-item strong{color:#1a3258;display:block;margin-bottom:2px}
.tab-container{background:#fff;border:1px solid #e6ebf1;border-radius:8px;overflow:hidden}
.tab-header{display:flex;background:#f8fafc;border-bottom:1px solid #e6ebf1}
.tab-btn{padding:14px 20px;font-size:14px;font-weight:700;color:#64748b;background:none;border:none;cursor:pointer;border-bottom:2px solid transparent}
.tab-btn.active{color:#1a3258;border-bottom-color:#1a3258;background:#fff}
.tab-content{padding:20px;display:none}
.tab-content.active{display:block}
.data-table{width:100%;border-collapse:collapse}
.data-table th{font-size:11px;text-transform:uppercase;color:#64748b;background:#f7f9fc;padding:10px 8px;text-align:left;border-bottom:1px solid #e6ebf1}
.data-table td{padding:10px 8px;border-top:1px solid #edf1f5;font-size:13px;vertical-align:middle}
.kpi-row{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:16px}
.kpi-card{border:1px solid #e6ebf1;padding:12px 16px;border-radius:6px;background:#fafafa}
.kpi-card b{display:block;font-size:18px;color:#1a3258}
.kpi-card span{font-size:11px;color:#718096}
</style>

<div class="profile-grid">
  <!-- Cột Trái: Thông tin chung -->
  <div class="profile-card">
    <div class="profile-avatar">
      <?php if(!empty($user['avatar'])): ?>
      <img src="/uploads/avatars/<?= e($user['avatar']) ?>">
      <?php else: ?>
      <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2NjYyI+PHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00czEuNzktNCA0LTRtMCAxMGMtMi42NyAwLTggMS4zNC04IDR2MmgxNnYtMmMwLTIuNjYtNS4zMy00LTgtNHoiLz48L3N2Zz4=">
      <?php endif; ?>
    </div>
    <h2 style="font-size:16px;font-weight:700;margin:0 0 5px;color:#1a3258"><?= e($user['full_name']) ?></h2>
    <span style="font-size:11px;background:#e2e8f0;color:#475569;padding:2px 8px;border-radius:999px;font-weight:700;text-transform:uppercase"><?= e($user['role']) ?></span>

    <div class="profile-info-list">
      <div class="profile-info-item"><strong>Số điện thoại</strong><?= e($user['phone'] ?: '—') ?></div>
      <div class="profile-info-item"><strong>Email</strong><?= e($user['email']) ?></div>
      <div class="profile-info-item"><strong>Địa chỉ</strong><?= e($user['address'] ?: '—') ?></div>
      <div class="profile-info-item"><strong>Ngày tham gia</strong><?= date('d/m/Y', strtotime($user['created_at'])) ?></div>
    </div>
    <div style="margin-top:20px">
      <a href="/admin/users/<?= (int)$user['id'] ?>/edit" class="btn btn-navy" style="display:block;text-align:center;font-size:12px">Sửa thông tin</a>
    </div>
  </div>

  <!-- Cột Phải: Tabs dữ liệu -->
  <div class="tab-container">
    <div class="tab-header">
      <button class="tab-btn active" onclick="openTab(event, 'tab-orders')">Đơn hàng đã mua</button>
      <button class="tab-btn" onclick="openTab(event, 'tab-garages')">Đội xe của KH (Garage)</button>
      <button class="tab-btn" onclick="openTab(event, 'tab-debt')">Lịch sử thu nợ</button>
    </div>

    <!-- Tab Đơn hàng -->
    <div id="tab-orders" class="tab-content active">
      <div class="kpi-row">
        <div class="kpi-card"><b><?= count($orders) ?></b><span>Tổng số đơn mua</span></div>
        <div class="kpi-card"><b style="color:#059669"><?= number_format($totalSpent) ?> đ</b><span>Tổng tiền đã thanh toán</span></div>
        <div class="kpi-card"><b style="color:#dc2626"><?= number_format($totalDebt) ?> đ</b><span>Công nợ chưa trả</span></div>
      </div>
      <div style="overflow:auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Mã đơn</th>
              <th>Ngày mua</th>
              <th>Hình thức</th>
              <th style="text-align:right">Tổng tiền</th>
              <th style="text-align:right">Đã trả</th>
              <th style="text-align:right">Còn nợ</th>
              <th>Thanh toán</th>
              <th>Vận chuyển</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($orders as $o): ?>
            <tr>
              <td style="font-family:monospace;font-weight:700"><a href="/admin/orders/<?= (int)$o['id'] ?>"><?= e($o['code']) ?></a></td>
              <td style="color:#6b7280"><?= e(substr($o['created_at'],0,10)) ?></td>
              <td><?= e($o['payment_method']) ?></td>
              <td style="text-align:right;font-weight:600"><?= number_format((int)$o['grand_total']) ?> đ</td>
              <td style="text-align:right;color:#059669"><?= number_format((int)$o['paid_amount']) ?> đ</td>
              <td style="text-align:right;color:#dc2626"><?= number_format((int)$o['remaining_amount']) ?> đ</td>
              <td>
                <span class="status-badge" style="background:<?= $o['payment_status']==='paid'?'#dcfce7':($o['payment_status']==='partial'?'#fef9c3':'#fee2e2') ?>;color:<?= $o['payment_status']==='paid'?'#166534':($o['payment_status']==='partial'?'#854d0e':'#b91c1c') ?>;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:700">
                  <?= $o['payment_status']==='paid'?'Đã thanh toán':($o['payment_status']==='partial'?'Trả một phần':'Chưa trả') ?>
                </span>
              </td>
              <td><?= e($o['shipping_status']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(!$orders): ?><tr><td colspan="8" style="padding:20px;text-align:center;color:#9ca3af">Khách hàng chưa mua đơn nào.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tab Đội xe (Garages) -->
    <div id="tab-garages" class="tab-content">
      <div style="overflow:auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Hãng xe</th>
              <th>Model</th>
              <th>Năm sản xuất</th>
              <th>Động cơ / Trim</th>
              <th>Nhãn xe</th>
              <th>Mặc định</th>
              <th>Ngày tạo</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($garages as $g): ?>
            <tr>
              <td><span style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;border-radius:4px;padding:2px 8px;font-size:12px;font-weight:700"><?= e($g['brand_name']) ?></span></td>
              <td style="font-weight:600"><?= e($g['model_name']) ?></td>
              <td><?= (int)$g['year'] ?></td>
              <td><?= e($g['trim'] ?: '—') ?></td>
              <td><?= e($g['label'] ?: '—') ?></td>
              <td>
                <?php if($g['is_default']): ?>
                <span style="background:#dcfce7;color:#166534;border:1px solid #bbf7d0;border-radius:4px;padding:2px 6px;font-size:11px;font-weight:700">Mặc định</span>
                <?php else: ?>
                <span style="color:#9ca3af">—</span>
                <?php endif; ?>
              </td>
              <td style="color:#9ca3af"><?= e(substr($g['created_at'],0,10)) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(!$garages): ?><tr><td colspan="7" style="padding:20px;text-align:center;color:#9ca3af">Khách hàng chưa đăng ký xe nào.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tab Lịch sử thu nợ -->
    <div id="tab-debt" class="tab-content">
      <div style="overflow:auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Mã phiếu thu</th>
              <th>Đơn hàng</th>
              <th>Ngày thu</th>
              <th>Số tiền thu</th>
              <th>Tài khoản quỹ</th>
              <th>Mã giao dịch</th>
              <th>Ghi chú</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($debtCollections as $d): ?>
            <tr>
              <td style="font-family:monospace;font-weight:700"><?= e($d['code']) ?></td>
              <td style="font-family:monospace"><a href="/admin/orders/<?= (int)$d['order_id'] ?>"><?= e($d['order_code']) ?></a></td>
              <td style="color:#6b7280"><?= e($d['collection_date']) ?></td>
              <td style="font-weight:700;color:#059669"><?= number_format((int)$d['amount']) ?> đ</td>
              <td><?= e($d['account_name']) ?></td>
              <td style="font-family:monospace"><?= e($d['reference_code'] ?: '—') ?></td>
              <td style="color:#4b5563;font-size:12px"><?= e($d['description'] ?: '—') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(!$debtCollections): ?><tr><td colspan="7" style="padding:20px;text-align:center;color:#9ca3af">Không có lịch sử thu nợ của khách hàng này.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
function openTab(evt, tabId) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tab-content");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].classList.remove("active");
  }
  tablinks = document.getElementsByClassName("tab-btn");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].classList.remove("active");
  }
  document.getElementById(tabId).classList.add("active");
  evt.currentTarget.classList.add("active");
}
</script>

<?php require __DIR__.'/../partials/dashboard-foot.php'; ?>
